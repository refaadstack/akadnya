<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

defineOptions({
    layout: undefined,
});

const props = defineProps<{
    order: null | {
        id: number;
        order_number: string;
        status: string;
        total_amount: string | number;
        payment_status?: string | null;
        paid_at?: string | null;
    };
}>();

const title =
    props.order?.status === 'paid'
        ? 'Pembayaran berhasil'
        : props.order?.status === 'failed'
          ? 'Pembayaran gagal'
          : 'Pembayaran sedang dicek';

const polling = ref(!props.order || props.order.status === 'pending');
const pollInterval = ref<ReturnType<typeof setInterval> | null>(null);

const checkStatus = () => {
    if (!props.order) {
        return;
    }

    router.reload({
        preserveUrl: true,
        onFinish: () => {
            if (props.order && props.order.status !== 'pending') {
                polling.value = false;

                if (pollInterval.value) {
                    clearInterval(pollInterval.value);
                    pollInterval.value = null;
                }
            }
        },
    });
};

onMounted(() => {
    if (polling.value) {
        pollInterval.value = setInterval(checkStatus, 5000);
    }
});

onUnmounted(() => {
    if (pollInterval.value) {
        clearInterval(pollInterval.value);
    }
});
</script>

<template>
    <Head :title="title" />

    <main class="min-h-screen bg-[var(--my-background)] px-4 py-12">
        <section class="my-card mx-auto max-w-xl p-8 text-center">
            <div
                class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full"
                :class="
                    order?.status === 'paid'
                        ? 'bg-green-100 text-green-700'
                        : order?.status === 'failed'
                          ? 'bg-red-100 text-red-700'
                          : 'bg-yellow-100 text-yellow-700'
                "
            >
                <svg
                    v-if="order?.status === 'paid'"
                    class="h-7 w-7"
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
                <svg
                    v-else
                    class="h-7 w-7"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 8v4l3 3"
                    />
                </svg>
            </div>

            <h1 class="my-heading mb-3 text-3xl">{{ title }}</h1>

            <p v-if="!order" class="mb-6 text-[var(--my-muted)]">
                Order tidak ditemukan. Silakan kembali ke dashboard dan cek
                riwayat order kamu.
            </p>
            <div v-else class="mb-6 space-y-3 text-left">
                <div
                    class="flex justify-between border-b border-[var(--my-border)] pb-3"
                >
                    <span class="text-[var(--my-muted)]">Order</span>
                    <strong>{{ order.order_number }}</strong>
                </div>
                <div
                    class="flex justify-between border-b border-[var(--my-border)] pb-3"
                >
                    <span class="text-[var(--my-muted)]">Status</span>
                    <strong>{{ order.status }}</strong>
                </div>
                <div
                    v-if="order.payment_status"
                    class="flex justify-between border-b border-[var(--my-border)] pb-3"
                >
                    <span class="text-[var(--my-muted)]"
                        >Status Pembayaran</span
                    >
                    <strong>{{ order.payment_status }}</strong>
                </div>
                <div
                    v-if="order.paid_at"
                    class="flex justify-between border-b border-[var(--my-border)] pb-3"
                >
                    <span class="text-[var(--my-muted)]">Dibayar pada</span>
                    <strong>{{
                        new Date(order.paid_at).toLocaleString('id-ID')
                    }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-[var(--my-muted)]">Total</span>
                    <strong
                        >Rp
                        {{
                            Number(order.total_amount).toLocaleString('id-ID')
                        }}</strong
                    >
                </div>
            </div>

            <p
                v-if="polling"
                class="mb-6 rounded-lg bg-yellow-50 p-4 text-sm text-yellow-800"
            >
                Sedang mengecek status pembayaran otomatis setiap 5 detik...
            </p>

            <div class="flex flex-col gap-3 sm:flex-row sm:justify-center">
                <Link href="/dashboard" class="my-btn-primary"
                    >Ke Dashboard</Link
                >
                <Link href="/dashboard/transactions" class="my-btn-secondary"
                    >Lihat Riwayat Transaksi</Link
                >
                <button
                    v-if="!polling"
                    type="button"
                    class="my-btn-secondary"
                    @click="checkStatus"
                >
                    Cek Status
                </button>
            </div>
        </section>
    </main>
</template>
