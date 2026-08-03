<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

// Disable default layout
defineOptions({
    layout: undefined,
});

interface CheckoutItem {
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

const props = defineProps<{
    items: CheckoutItem[];
    totals: Totals;
}>();

const isSubmitting = ref(false);
const showUserMenu = ref(false);

const logout = () => {
    router.post('/logout');
};

const hasDiscount = (
    item: CheckoutItem,
): item is CheckoutItem & { original_price: number } =>
    item.original_price != null && item.original_price > item.price;

const total = computed(() => Number(props.totals.subtotal));

const hasTemplate = computed(() =>
    props.items.some((item) => item.type === 'template'),
);

const appDomain = usePage().props.appDomain as string;

const templateFeatures = computed(() => [
    'Akses editor undangan lengkap',
    `Subdomain gratis (namaanda.${appDomain})`,
    'RSVP, ucapan, galeri foto, dan amplop digital',
    'Publish undangan & bagikan link',
]);

// Submit order — order is created from the server-side cart
const submitOrder = async () => {
    if (isSubmitting.value) {
        return;
    }

    isSubmitting.value = true;

    try {
        // Get CSRF token from cookie (always available in Laravel)
        const xsrfToken = decodeURIComponent(
            document.cookie
                .split('; ')
                .find((row) => row.startsWith('XSRF-TOKEN='))
                ?.split('=')[1] ?? '',
        );

        const response = await fetch('/checkout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-XSRF-TOKEN': xsrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({}),
        });

        const data = await response.json();

        if (!response.ok) {
            // Show validation errors or server error message
            const message =
                data.message ||
                (data.errors
                    ? Object.values(data.errors).flat().join('\n')
                    : 'Terjadi kesalahan saat membuat order.');
            alert(message);
            isSubmitting.value = false;

            return;
        }

        if (data.payment_url) {
            window.location.href = data.payment_url;
        } else {
            alert('Gagal mendapatkan URL pembayaran. Silakan coba lagi.');
            isSubmitting.value = false;
        }
    } catch (error) {
        console.error('Checkout error:', error);
        alert('Terjadi kesalahan koneksi. Silakan coba lagi.');
        isSubmitting.value = false;
    }
};
</script>

<template>
    <div>
        <Head title="Checkout" />

        <div class="my-page">
            <!-- Navigation -->
            <nav
                class="border-b border-[var(--my-border)] bg-[var(--my-background)]/86 backdrop-blur-md"
            >
                <div class="container mx-auto px-4">
                    <div class="flex h-16 items-center justify-between">
                        <!-- Logo -->
                        <Link
                            href="/dashboard"
                            class="flex items-center space-x-2"
                        >
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-lg bg-[var(--my-primary)]"
                            >
                                <span class="text-xl font-bold text-white"
                                    >M</span
                                >
                            </div>
                            <span
                                class="font-display text-2xl font-bold text-[var(--my-primary)]"
                            >
                                MyAkad
                            </span>
                        </Link>

                        <!-- Navigation Links -->
                        <div class="hidden items-center space-x-8 md:flex">
                            <Link
                                href="/dashboard"
                                class="font-medium text-[var(--my-neutral)] transition hover:text-[var(--my-primary)]"
                            >
                                Dashboard
                            </Link>
                            <Link
                                href="/templates"
                                class="font-medium text-[var(--my-neutral)] transition hover:text-[var(--my-primary)]"
                            >
                                Template
                            </Link>
                            <Link
                                href="/produk"
                                class="font-medium text-[var(--my-neutral)] transition hover:text-[var(--my-primary)]"
                            >
                                Produk
                            </Link>
                        </div>

                        <!-- User Menu -->
                        <div class="relative">
                            <button
                                @click="showUserMenu = !showUserMenu"
                                class="flex items-center space-x-3 focus:outline-none"
                            >
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-[var(--my-primary)]"
                                >
                                    <span
                                        class="text-sm font-semibold text-white"
                                    >
                                        {{
                                            $page.props.auth?.user?.name
                                                ?.charAt(0)
                                                .toUpperCase() || 'U'
                                        }}
                                    </span>
                                </div>
                                <svg
                                    class="h-4 w-4 text-[var(--my-muted)]"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 9l-7 7-7-7"
                                    />
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div
                                v-show="showUserMenu"
                                @click="showUserMenu = false"
                                class="my-card absolute right-0 z-50 mt-2 w-48 py-2"
                            >
                                <div class="border-b border-gray-100 px-4 py-2">
                                    <p
                                        class="text-sm font-semibold text-[var(--my-neutral)]"
                                    >
                                        {{ $page.props.auth?.user?.name }}
                                    </p>
                                    <p class="text-xs text-[var(--my-muted)]">
                                        {{ $page.props.auth?.user?.email }}
                                    </p>
                                </div>

                                <Link
                                    href="/settings/profile"
                                    class="block px-4 py-2 text-sm text-[var(--my-neutral)] transition hover:bg-[var(--my-surface-soft)]"
                                >
                                    <div class="flex items-center">
                                        <svg
                                            class="mr-2 h-4 w-4"
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
                                    </div>
                                </Link>

                                <Link
                                    href="/settings/security"
                                    class="block px-4 py-2 text-sm text-[var(--my-neutral)] transition hover:bg-[var(--my-surface-soft)]"
                                >
                                    <div class="flex items-center">
                                        <svg
                                            class="mr-2 h-4 w-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                            />
                                        </svg>
                                        Keamanan
                                    </div>
                                </Link>

                                <div class="mt-2 border-t border-gray-100 pt-2">
                                    <button
                                        @click="logout"
                                        class="block w-full px-4 py-2 text-left text-sm text-red-600 transition hover:bg-red-50"
                                    >
                                        <div class="flex items-center">
                                            <svg
                                                class="mr-2 h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                                />
                                            </svg>
                                            Keluar
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="min-h-screen py-8">
                <div class="container mx-auto max-w-4xl px-4">
                    <h1 class="my-heading mb-8 text-4xl">Checkout</h1>
                    <div class="grid gap-8 lg:grid-cols-3">
                        <!-- Item Summary -->
                        <div class="space-y-6 lg:col-span-2">
                            <div class="my-card p-6">
                                <p class="my-label mb-4">
                                    Item di Keranjang ({{ items.length }})
                                </p>
                                <div
                                    v-for="(item, index) in items"
                                    :key="item.id"
                                    class="flex items-center justify-between gap-4 py-3"
                                    :class="
                                        index !== items.length - 1
                                            ? 'border-b border-[var(--my-border)]'
                                            : ''
                                    "
                                >
                                    <div class="min-w-0">
                                        <h2 class="my-heading text-xl">
                                            {{ item.name }}
                                        </h2>
                                        <p
                                            class="text-sm text-[var(--my-muted)]"
                                        >
                                            {{ item.description }}
                                            <span
                                                v-if="item.quantity > 1"
                                                class="font-semibold text-[var(--my-neutral)]"
                                            >
                                                × {{ item.quantity }}
                                            </span>
                                        </p>
                                    </div>
                                    <div class="ml-4 shrink-0 text-right">
                                        <p
                                            class="text-lg font-bold text-[var(--my-primary)]"
                                        >
                                            {{
                                                item.is_free
                                                    ? 'Gratis'
                                                    : `Rp ${(item.price * item.quantity).toLocaleString('id-ID')}`
                                            }}
                                        </p>
                                        <p
                                            v-if="
                                                hasDiscount(item) &&
                                                !item.is_free
                                            "
                                            class="text-sm font-semibold text-[var(--my-muted)] line-through"
                                        >
                                            Rp
                                            {{
                                                (
                                                    item.original_price *
                                                    item.quantity
                                                ).toLocaleString('id-ID')
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <Link
                                    href="/keranjang"
                                    class="mt-4 inline-block text-sm font-semibold text-[var(--my-primary)] hover:underline"
                                >
                                    Ubah Keranjang
                                </Link>
                            </div>

                            <!-- Template features (price includes everything) -->
                            <div v-if="hasTemplate" class="my-card p-6">
                                <h2 class="my-heading mb-4 text-2xl">
                                    Sudah Termasuk
                                </h2>
                                <ul class="space-y-3">
                                    <li
                                        v-for="feature in templateFeatures"
                                        :key="feature"
                                        class="flex items-center gap-3 text-sm text-[var(--my-neutral)]"
                                    >
                                        <span
                                            class="grid size-6 shrink-0 place-items-center rounded-full bg-[var(--my-primary)]/10 text-[var(--my-primary)]"
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
                                                    d="M5 13l4 4L19 7"
                                                />
                                            </svg>
                                        </span>
                                        {{ feature }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Order Total & Payment -->
                        <div class="lg:col-span-1">
                            <div class="my-card sticky top-24 p-6">
                                <h2 class="my-heading mb-4 text-2xl">
                                    Ringkasan Order
                                </h2>

                                <div class="mb-4 space-y-2">
                                    <div
                                        v-for="item in items"
                                        :key="item.id"
                                        class="flex justify-between gap-3 text-sm"
                                    >
                                        <span class="text-[var(--my-muted)]"
                                            >{{ item.name }}
                                            <span
                                                v-if="item.quantity > 1"
                                                class="font-semibold text-[var(--my-neutral)]"
                                            >
                                                × {{ item.quantity }}
                                            </span>
                                        </span>
                                        <span class="font-medium">{{
                                            item.is_free
                                                ? 'Gratis'
                                                : `Rp ${(item.price * item.quantity).toLocaleString('id-ID')}`
                                        }}</span>
                                    </div>
                                    <div
                                        v-if="totals.savings > 0"
                                        class="flex justify-between text-sm text-emerald-600"
                                    >
                                        <span>Kamu hemat</span>
                                        <span class="font-semibold">
                                            -Rp
                                            {{
                                                totals.savings.toLocaleString(
                                                    'id-ID',
                                                )
                                            }}
                                        </span>
                                    </div>
                                </div>

                                <div class="mb-6 border-t pt-4">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span
                                            class="text-lg font-bold text-[var(--my-neutral)]"
                                            >Total</span
                                        >
                                        <span
                                            class="text-2xl font-bold text-[var(--my-primary)]"
                                        >
                                            Rp
                                            {{ total.toLocaleString('id-ID') }}
                                        </span>
                                    </div>
                                </div>

                                <button
                                    @click="submitOrder"
                                    :disabled="isSubmitting"
                                    class="my-btn-primary w-full disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {{
                                        isSubmitting
                                            ? 'Memproses...'
                                            : 'Lanjut ke Pembayaran'
                                    }}
                                </button>

                                <p
                                    class="mt-4 text-center text-xs text-[var(--my-muted)]"
                                >
                                    Dengan melanjutkan, Anda menyetujui syarat
                                    dan ketentuan kami
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
