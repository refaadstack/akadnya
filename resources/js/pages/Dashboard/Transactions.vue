<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ChevronDown } from 'lucide-vue-next';
import { ref } from 'vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';

interface OrderItem {
    name: string;
    price: number;
    quantity: number;
}

interface PaymentInfo {
    status: string | null;
    payment_method: string | null;
    provider_transaction_id: string | null;
    paid_at: string | null;
}

interface Order {
    id: number;
    order_number: string;
    status: string;
    total_amount: number;
    subtotal_amount: number;
    payment_gateway_fee: number;
    tax_amount: number;
    paid_at: string | null;
    created_at: string;
    payment: PaymentInfo | null;
    items: OrderItem[];
}

defineProps<{
    orders: Order[];
}>();

const expandedOrder = ref<number | null>(null);

const toggleExpand = (orderId: number) => {
    expandedOrder.value = expandedOrder.value === orderId ? null : orderId;
};

const formatCurrency = (amount: number) =>
    `Rp ${amount.toLocaleString('id-ID')}`;

const formatDate = (dateString: string | null) => {
    if (!dateString) {
        return '-';
    }

    return new Date(dateString).toLocaleString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getOrderStatusBadge = (status: string) => {
    switch (status) {
        case 'paid':
            return {
                class: 'bg-green-100 text-green-800 border-green-200',
                label: 'Lunas',
            };
        case 'pending':
            return {
                class: 'bg-yellow-100 text-yellow-800 border-yellow-200',
                label: 'Menunggu Pembayaran',
            };
        case 'failed':
            return {
                class: 'bg-red-100 text-red-800 border-red-200',
                label: 'Gagal',
            };
        case 'refunded':
            return {
                class: 'bg-gray-100 text-gray-700 border-gray-200',
                label: 'Dikembalikan',
            };
        case 'expired':
            return {
                class: 'bg-gray-100 text-gray-700 border-gray-200',
                label: 'Kedaluwarsa',
            };
        default:
            return {
                class: 'bg-gray-100 text-gray-700 border-gray-200',
                label: status,
            };
    }
};

const getPaymentStatusBadge = (status: string | null) => {
    switch (status) {
        case 'paid':
            return {
                class: 'bg-green-100 text-green-800 border-green-200',
                label: 'Settlement',
            };
        case 'pending':
            return {
                class: 'bg-yellow-100 text-yellow-800 border-yellow-200',
                label: 'Pending',
            };
        case 'failed':
            return {
                class: 'bg-red-100 text-red-800 border-red-200',
                label: 'Gagal',
            };
        case 'refunded':
            return {
                class: 'bg-gray-100 text-gray-700 border-gray-200',
                label: 'Refund',
            };
        case 'expired':
            return {
                class: 'bg-gray-100 text-gray-700 border-gray-200',
                label: 'Kedaluwarsa',
            };
        default:
            return null;
    }
};

const formatPaymentMethod = (method: string | null) => {
    if (!method) {
        return '-';
    }

    return method
        .split('_')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
};
</script>

<template>
    <DashboardLayout>
        <Head title="Transaksi" />

        <div class="py-8">
            <div class="container mx-auto px-4">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Transaksi</h1>
                    <p class="mt-1 text-gray-600">
                        Riwayat pembelian dan status pembayaran Anda
                    </p>
                </div>

                <div
                    v-if="orders.length === 0"
                    class="rounded-xl border border-gray-200 bg-white p-12 text-center shadow-sm"
                >
                    <p class="text-lg font-semibold text-gray-900">
                        Belum ada transaksi
                    </p>
                    <p class="mt-2 text-sm text-gray-600">
                        Transaksi akan muncul di sini setelah Anda melakukan
                        pembelian.
                    </p>
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="order in orders"
                        :key="order.id"
                        class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
                    >
                        <button
                            type="button"
                            class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left transition hover:bg-gray-50"
                            @click="toggleExpand(order.id)"
                        >
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-semibold text-gray-900">
                                        {{ order.order_number }}
                                    </p>
                                    <span
                                        class="rounded-full border px-2.5 py-0.5 text-xs font-semibold"
                                        :class="
                                            getOrderStatusBadge(order.status)
                                                .class
                                        "
                                    >
                                        {{
                                            getOrderStatusBadge(order.status)
                                                .label
                                        }}
                                    </span>
                                    <span
                                        v-if="
                                            getPaymentStatusBadge(
                                                order.payment?.status ?? null,
                                            )
                                        "
                                        class="rounded-full border px-2.5 py-0.5 text-xs font-semibold"
                                        :class="
                                            getPaymentStatusBadge(
                                                order.payment?.status ?? null,
                                            )!.class
                                        "
                                    >
                                        {{
                                            getPaymentStatusBadge(
                                                order.payment?.status ?? null,
                                            )!.label
                                        }}
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ formatDate(order.created_at) }}
                                </p>
                            </div>

                            <div class="flex shrink-0 items-center gap-3">
                                <p class="font-bold text-gray-900">
                                    {{ formatCurrency(order.total_amount) }}
                                </p>
                                <ChevronDown
                                    class="size-5 text-gray-400 transition-transform"
                                    :class="
                                        expandedOrder === order.id
                                            ? 'rotate-180'
                                            : ''
                                    "
                                />
                            </div>
                        </button>

                        <div
                            v-if="expandedOrder === order.id"
                            class="border-t border-gray-200 bg-gray-50 px-6 py-5"
                        >
                            <div class="grid gap-6 md:grid-cols-2">
                                <div>
                                    <p
                                        class="mb-3 text-sm font-semibold text-gray-900"
                                    >
                                        Detail Item
                                    </p>
                                    <div class="space-y-2">
                                        <div
                                            v-for="(item, index) in order.items"
                                            :key="index"
                                            class="flex items-center justify-between text-sm"
                                        >
                                            <span class="text-gray-600">
                                                {{ item.name }}
                                                <span
                                                    v-if="item.quantity > 1"
                                                    class="text-gray-400"
                                                >
                                                    × {{ item.quantity }}
                                                </span>
                                            </span>
                                            <span
                                                class="font-semibold text-gray-900"
                                            >
                                                {{
                                                    formatCurrency(
                                                        item.price *
                                                            item.quantity,
                                                    )
                                                }}
                                            </span>
                                        </div>
                                    </div>

                                    <div
                                        class="mt-4 space-y-1.5 border-t border-gray-200 pt-3 text-sm"
                                    >
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span class="text-gray-600"
                                                >Subtotal</span
                                            >
                                            <span class="text-gray-900">{{
                                                formatCurrency(
                                                    order.subtotal_amount,
                                                )
                                            }}</span>
                                        </div>
                                        <div
                                            v-if="order.payment_gateway_fee > 0"
                                            class="flex items-center justify-between"
                                        >
                                            <span class="text-gray-600"
                                                >Biaya Payment Gateway</span
                                            >
                                            <span class="text-gray-900">{{
                                                formatCurrency(
                                                    order.payment_gateway_fee,
                                                )
                                            }}</span>
                                        </div>
                                        <div
                                            v-if="order.tax_amount > 0"
                                            class="flex items-center justify-between"
                                        >
                                            <span class="text-gray-600"
                                                >Pajak (PPN)</span
                                            >
                                            <span class="text-gray-900">{{
                                                formatCurrency(order.tax_amount)
                                            }}</span>
                                        </div>
                                        <div
                                            class="flex items-center justify-between font-bold"
                                        >
                                            <span class="text-gray-900"
                                                >Total</span
                                            >
                                            <span class="text-gray-900">{{
                                                formatCurrency(
                                                    order.total_amount,
                                                )
                                            }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <p
                                        class="mb-3 text-sm font-semibold text-gray-900"
                                    >
                                        Informasi Pembayaran
                                    </p>
                                    <div class="space-y-2 text-sm">
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span class="text-gray-600"
                                                >Metode Pembayaran</span
                                            >
                                            <span
                                                class="font-semibold text-gray-900"
                                            >
                                                {{
                                                    formatPaymentMethod(
                                                        order.payment
                                                            ?.payment_method ??
                                                            null,
                                                    )
                                                }}
                                            </span>
                                        </div>
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span class="text-gray-600"
                                                >ID Transaksi</span
                                            >
                                            <span
                                                class="font-semibold text-gray-900"
                                            >
                                                {{
                                                    order.payment
                                                        ?.provider_transaction_id ??
                                                    '-'
                                                }}
                                            </span>
                                        </div>
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span class="text-gray-600"
                                                >Dibayar pada</span
                                            >
                                            <span
                                                class="font-semibold text-gray-900"
                                            >
                                                {{
                                                    formatDate(
                                                        order.payment
                                                            ?.paid_at ?? null,
                                                    )
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
