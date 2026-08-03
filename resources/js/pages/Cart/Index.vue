<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Minus, Plus, ShoppingBag, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import PublicFooter from '@/components/PublicFooter.vue';
import PublicNavbar from '@/components/PublicNavbar.vue';

interface CartItem {
    id: number;
    type: 'template' | 'product';
    item_id: number;
    slug: string;
    name: string;
    description: string;
    price: number;
    original_price?: number | null;
    discount_percent?: number;
    quantity: number;
    is_free?: boolean;
}

interface Totals {
    item_count: number;
    subtotal: number;
    original_subtotal?: number | null;
    savings: number;
}

defineProps<{
    items: CartItem[];
    totals: Totals;
}>();

defineOptions({
    layout: undefined,
});

const hasDiscount = (
    item: CartItem,
): item is CartItem & { original_price: number } =>
    item.original_price != null && item.original_price > item.price;

const updatingId = ref<number | null>(null);

const changeQuantity = (item: CartItem, delta: number) => {
    const next = item.quantity + delta;

    if (next < 1 || next > 10) {
        return;
    }

    updatingId.value = item.id;
    router.patch(
        `/keranjang/${item.id}`,
        { quantity: next },
        {
            preserveScroll: true,
            onFinish: () => {
                updatingId.value = null;
            },
        },
    );
};

const removeItem = (item: CartItem) => {
    router.delete(`/keranjang/${item.id}`, {
        preserveScroll: true,
    });
};

const clearCart = () => {
    router.delete('/keranjang', {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="my-page">
        <Head title="Keranjang" />

        <PublicNavbar current-page="cart" />

        <main class="pt-28">
            <section class="my-container py-14">
                <div class="max-w-3xl">
                    <p class="my-label mb-4">Keranjang</p>
                    <h1 class="my-heading text-5xl leading-tight">
                        Item pilihanmu di sini.
                    </h1>
                    <p class="my-copy mt-5">
                        Template dan tambahan yang kamu pilih akan dibayar dalam
                        satu kali checkout.
                    </p>
                </div>
            </section>

            <section class="my-container pb-20">
                <template v-if="items.length > 0">
                    <div
                        class="grid items-start gap-8 lg:grid-cols-[1.6fr_1fr]"
                    >
                        <div class="space-y-5">
                            <article
                                v-for="item in items"
                                :key="item.id"
                                class="my-card p-5"
                            >
                                <div class="flex flex-col gap-4 sm:flex-row">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <h3 class="my-heading text-2xl">
                                                {{ item.name }}
                                            </h3>
                                            <span
                                                v-if="item.type === 'template'"
                                                class="rounded-full bg-[var(--my-primary)]/10 px-2.5 py-0.5 text-xs font-bold text-[var(--my-primary)]"
                                            >
                                                Template
                                            </span>
                                        </div>
                                        <p
                                            class="mt-1 text-sm text-[var(--my-muted)]"
                                        >
                                            {{ item.description }}
                                        </p>

                                        <div
                                            class="mt-3 flex flex-wrap items-baseline gap-x-2"
                                        >
                                            <p
                                                class="text-2xl font-bold text-[var(--my-primary)]"
                                            >
                                                {{
                                                    item.is_free
                                                        ? 'Gratis'
                                                        : `Rp ${item.price.toLocaleString('id-ID')}`
                                                }}
                                            </p>
                                            <p
                                                v-if="
                                                    hasDiscount(item) &&
                                                    !item.is_free
                                                "
                                                class="text-base font-semibold text-[var(--my-muted)] line-through"
                                            >
                                                Rp
                                                {{
                                                    item.original_price.toLocaleString(
                                                        'id-ID',
                                                    )
                                                }}
                                            </p>
                                            <span
                                                v-if="
                                                    item.discount_percent &&
                                                    item.discount_percent > 0 &&
                                                    !item.is_free
                                                "
                                                class="my-badge"
                                                >-{{
                                                    item.discount_percent
                                                }}%</span
                                            >
                                        </div>
                                    </div>

                                    <div
                                        class="flex shrink-0 items-center gap-4 sm:flex-col sm:items-end"
                                    >
                                        <div
                                            v-if="item.type === 'product'"
                                            class="flex items-center gap-2"
                                        >
                                            <button
                                                type="button"
                                                class="grid size-8 place-items-center rounded-full border border-[var(--my-border)] text-[var(--my-neutral)] transition hover:text-[var(--my-primary)] disabled:opacity-40"
                                                :disabled="
                                                    item.quantity <= 1 ||
                                                    updatingId === item.id
                                                "
                                                aria-label="Kurangi"
                                                @click="
                                                    changeQuantity(item, -1)
                                                "
                                            >
                                                <Minus class="size-4" />
                                            </button>
                                            <span
                                                class="w-8 text-center font-bold"
                                            >
                                                {{ item.quantity }}
                                            </span>
                                            <button
                                                type="button"
                                                class="grid size-8 place-items-center rounded-full border border-[var(--my-border)] text-[var(--my-neutral)] transition hover:text-[var(--my-primary)] disabled:opacity-40"
                                                :disabled="
                                                    item.quantity >= 10 ||
                                                    updatingId === item.id
                                                "
                                                aria-label="Tambah"
                                                @click="changeQuantity(item, 1)"
                                            >
                                                <Plus class="size-4" />
                                            </button>
                                        </div>
                                        <p
                                            v-if="item.type === 'product'"
                                            class="text-sm font-semibold text-[var(--my-muted)]"
                                        >
                                            Subtotal: Rp
                                            {{
                                                (
                                                    item.price * item.quantity
                                                ).toLocaleString('id-ID')
                                            }}
                                        </p>

                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-1.5 text-sm font-semibold text-red-600 transition hover:text-red-700"
                                            @click="removeItem(item)"
                                        >
                                            <Trash2 class="size-4" />
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </article>

                            <div class="flex justify-end">
                                <button
                                    type="button"
                                    class="text-sm font-semibold text-[var(--my-muted)] underline-offset-4 transition hover:text-red-600 hover:underline"
                                    @click="clearCart"
                                >
                                    Kosongkan keranjang
                                </button>
                            </div>
                        </div>

                        <aside class="my-card sticky top-24 p-6">
                            <h2 class="my-heading mb-4 text-2xl">Ringkasan</h2>

                            <div class="space-y-3 text-sm">
                                <div
                                    class="flex justify-between text-[var(--my-muted)]"
                                >
                                    <span>Jumlah item</span>
                                    <span class="font-semibold">
                                        {{ totals.item_count }}
                                    </span>
                                </div>
                                <div
                                    v-if="totals.original_subtotal"
                                    class="flex justify-between text-[var(--my-muted)]"
                                >
                                    <span>Harga normal</span>
                                    <span class="line-through">
                                        Rp
                                        {{
                                            totals.original_subtotal.toLocaleString(
                                                'id-ID',
                                            )
                                        }}
                                    </span>
                                </div>
                                <div
                                    v-if="totals.savings > 0"
                                    class="flex justify-between text-emerald-600"
                                >
                                    <span>Kamu hemat</span>
                                    <span class="font-semibold">
                                        Rp
                                        {{
                                            totals.savings.toLocaleString(
                                                'id-ID',
                                            )
                                        }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-5 border-t pt-5">
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-lg font-bold text-[var(--my-neutral)]"
                                        >Total</span
                                    >
                                    <span
                                        class="text-2xl font-bold text-[var(--my-primary)]"
                                    >
                                        Rp
                                        {{
                                            totals.subtotal.toLocaleString(
                                                'id-ID',
                                            )
                                        }}
                                    </span>
                                </div>
                            </div>

                            <Link
                                href="/checkout"
                                class="my-btn-primary mt-6 w-full gap-2"
                            >
                                <ShoppingBag class="size-4" />
                                Lanjut ke Checkout
                            </Link>
                            <Link
                                href="/templates"
                                class="my-btn-secondary mt-3 w-full"
                            >
                                Tambah Item Lain
                            </Link>
                        </aside>
                    </div>
                </template>

                <div v-else class="my-card mx-auto max-w-md py-16 text-center">
                    <p class="my-heading text-3xl">Keranjang masih kosong</p>
                    <p class="my-copy mt-3">
                        Jelajahi koleksi template atau produk tambahan, lalu
                        tambahkan ke keranjang.
                    </p>
                    <div class="mt-6 grid gap-3">
                        <Link href="/templates" class="my-btn-primary w-full"
                            >Lihat Koleksi Template</Link
                        >
                        <Link href="/produk" class="my-btn-secondary w-full"
                            >Lihat Produk Tambahan</Link
                        >
                    </div>
                </div>
            </section>
        </main>

        <PublicFooter />
    </div>
</template>
