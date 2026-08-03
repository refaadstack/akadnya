<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

// Disable default layout
defineOptions({
    layout: undefined,
});

interface CheckoutItem {
    type: 'template' | 'product';
    id: number;
    slug: string;
    name: string;
    description: string;
    price: number;
    is_free?: boolean;
    is_recurring?: boolean;
    recurring_interval?: string;
}

const props = defineProps<{
    item: CheckoutItem;
}>();

const previewData = ref<Record<string, any> | null>(null);
const isSubmitting = ref(false);
const showUserMenu = ref(false);

const logout = () => {
    router.post('/logout');
};

// Load preview data from sessionStorage (only for templates)
onMounted(() => {
    if (props.item.type !== 'template') {
        return;
    }

    try {
        const STORAGE_KEY = `preview_data_${props.item.slug}`;
        const stored = sessionStorage.getItem(STORAGE_KEY);

        if (stored) {
            const { data } = JSON.parse(stored);
            previewData.value = data;
        }
    } catch (e) {
        console.error('Failed to load preview data:', e);
    }
});

const total = computed(() => Number(props.item.price));

const isRecurring = computed(
    () => props.item.type === 'product' && props.item.is_recurring,
);

const templateFeatures = [
    'Akses editor undangan lengkap',
    'Subdomain gratis (namaanda.myakad.id)',
    'RSVP, ucapan, galeri foto, dan amplop digital',
    'Publish undangan & bagikan link',
];

// Submit order
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

        const body: Record<string, any> = {
            preview_data: previewData.value,
        };

        if (props.item.type === 'template') {
            body.template_id = props.item.id;
        } else {
            body.product_id = props.item.id;
        }

        const response = await fetch('/checkout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-XSRF-TOKEN': xsrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(body),
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
                                <p class="my-label mb-2">
                                    {{
                                        item.type === 'template'
                                            ? 'Template Terpilih'
                                            : 'Produk Terpilih'
                                    }}
                                </p>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h2 class="my-heading text-2xl">
                                            {{ item.name }}
                                        </h2>
                                        <p
                                            class="text-sm text-[var(--my-muted)]"
                                        >
                                            {{ item.description }}
                                        </p>
                                    </div>
                                    <div class="ml-4 text-right">
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
                                            v-if="isRecurring"
                                            class="mt-1 text-xs text-[var(--my-muted)]"
                                        >
                                            /{{
                                                item.recurring_interval ===
                                                'monthly'
                                                    ? 'bulan'
                                                    : 'tahun'
                                            }}
                                        </p>
                                        <p
                                            v-else-if="item.type === 'product'"
                                            class="mt-1 text-xs text-[var(--my-muted)]"
                                        >
                                            Sekali bayar
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Template features (price includes everything) -->
                            <div
                                v-if="item.type === 'template'"
                                class="my-card p-6"
                            >
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

                            <!-- Preview Data Info (templates only) -->
                            <div
                                v-if="item.type === 'template'"
                                class="rounded-lg border border-blue-200 bg-blue-50 p-4"
                            >
                                <div class="flex items-start">
                                    <svg
                                        class="mt-0.5 mr-3 h-5 w-5 text-blue-600"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    <div>
                                        <p
                                            class="text-sm font-semibold text-blue-900"
                                        >
                                            Data preview Anda tersimpan
                                        </p>
                                        <p class="mt-1 text-sm text-blue-700">
                                            Data yang Anda input di halaman
                                            preview akan otomatis digunakan
                                            untuk undangan Anda setelah
                                            pembayaran berhasil.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-else
                                class="rounded-lg border border-yellow-200 bg-yellow-50 p-4"
                            >
                                <div class="flex items-start">
                                    <svg
                                        class="mt-0.5 mr-3 h-5 w-5 text-yellow-600"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    <div>
                                        <p
                                            class="text-sm font-semibold text-yellow-900"
                                        >
                                            Belum ada data preview
                                        </p>
                                        <p class="mt-1 text-sm text-yellow-700">
                                            Anda bisa mengisi data undangan
                                            nanti setelah pembayaran, atau
                                            kembali ke halaman preview untuk
                                            mengisi data terlebih dahulu.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Total & Payment -->
                        <div class="lg:col-span-1">
                            <div class="my-card sticky top-24 p-6">
                                <h2 class="my-heading mb-4 text-2xl">
                                    Ringkasan Order
                                </h2>

                                <div class="mb-4 space-y-3">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-[var(--my-muted)]">{{
                                            item.name
                                        }}</span>
                                        <span class="font-medium">{{
                                            item.is_free
                                                ? 'Gratis'
                                                : `Rp ${item.price.toLocaleString('id-ID')}`
                                        }}</span>
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
                                            {{
                                                item.is_free
                                                    ? 'Gratis'
                                                    : `Rp ${total.toLocaleString('id-ID')}`
                                            }}
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
