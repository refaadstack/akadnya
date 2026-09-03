<script setup lang="ts">
import { ref } from 'vue';

const props = defineProps<{
    content: any;
}>();

const showQris = ref(false);
const copiedAccount = ref(false);

const hasGiftOptions =
    props.content.bank_name ||
    props.content.qris_image_url ||
    props.content.gopay_number ||
    props.content.ovo_number ||
    props.content.dana_number;

const copyAccountNumber = () => {
    if (props.content.account_number) {
        navigator.clipboard.writeText(props.content.account_number);
        copiedAccount.value = true;
        setTimeout(() => {
            copiedAccount.value = false;
        }, 2000);
    }
};
</script>

<template>
    <section v-if="hasGiftOptions" class="bg-white px-4 py-20">
        <div class="container mx-auto max-w-4xl">
            <!-- Title -->
            <div class="mb-16 text-center">
                <h2
                    class="mb-4 font-serif text-4xl font-bold text-amber-800 md:text-5xl"
                >
                    Amplop Digital
                </h2>
                <div class="mb-4 flex items-center justify-center">
                    <div class="h-px w-20 bg-amber-400"></div>
                    <svg
                        class="mx-4 h-8 w-8 text-amber-600"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                        <path
                            fill-rule="evenodd"
                            d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    <div class="h-px w-20 bg-amber-400"></div>
                </div>
                <p class="mx-auto max-w-2xl text-gray-600">
                    Doa restu Anda merupakan karunia yang sangat berarti bagi
                    kami. Namun jika memberi adalah ungkapan tanda kasih, Anda
                    dapat memberi kado secara cashless.
                </p>
            </div>

            <!-- Gift Options -->
            <div class="space-y-6">
                <!-- Bank Transfer -->
                <div
                    v-if="content.bank_name"
                    class="rounded-2xl border-4 border-amber-200 bg-gradient-to-br from-amber-50 to-white p-8 shadow-xl"
                >
                    <div class="mb-6 flex items-center justify-center">
                        <div class="rounded-full bg-amber-600 p-4 text-white">
                            <svg
                                class="h-8 w-8"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"
                                />
                                <path
                                    fill-rule="evenodd"
                                    d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                    </div>

                    <h3
                        class="mb-6 text-center font-serif text-2xl font-bold text-amber-900"
                    >
                        Transfer Bank
                    </h3>

                    <div class="space-y-4">
                        <div class="text-center">
                            <p class="mb-1 text-sm text-gray-600">Bank</p>
                            <p class="text-xl font-bold text-amber-900">
                                {{ content.bank_name }}
                            </p>
                        </div>

                        <div class="text-center">
                            <p class="mb-1 text-sm text-gray-600">
                                Nomor Rekening
                            </p>
                            <div
                                class="flex items-center justify-center space-x-2"
                            >
                                <p
                                    class="text-2xl font-bold tracking-wider text-amber-900"
                                >
                                    {{ content.account_number }}
                                </p>
                                <button
                                    @click="copyAccountNumber"
                                    class="rounded-lg bg-amber-600 p-2 text-white transition hover:bg-amber-700"
                                    :title="
                                        copiedAccount ? 'Tersalin!' : 'Salin'
                                    "
                                >
                                    <svg
                                        v-if="!copiedAccount"
                                        class="h-5 w-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                                        />
                                    </svg>
                                    <svg
                                        v-else
                                        class="h-5 w-5"
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
                                </button>
                            </div>
                        </div>

                        <div v-if="content.account_name" class="text-center">
                            <p class="mb-1 text-sm text-gray-600">Atas Nama</p>
                            <p class="text-lg font-semibold text-amber-900">
                                {{ content.account_name }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- QRIS -->
                <div
                    v-if="content.qris_image_url"
                    class="rounded-2xl border-4 border-amber-200 bg-gradient-to-br from-amber-50 to-white p-8 shadow-xl"
                >
                    <div class="mb-6 flex items-center justify-center">
                        <div class="rounded-full bg-amber-600 p-4 text-white">
                            <svg
                                class="h-8 w-8"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M3 4a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm2 2V5h1v1H5zM3 13a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1v-3zm2 2v-1h1v1H5zM13 3a1 1 0 00-1 1v3a1 1 0 001 1h3a1 1 0 001-1V4a1 1 0 00-1-1h-3zm1 2v1h1V5h-1z"
                                    clip-rule="evenodd"
                                />
                                <path
                                    d="M11 4a1 1 0 10-2 0v1a1 1 0 002 0V4zM10 7a1 1 0 011 1v1h2a1 1 0 110 2h-3a1 1 0 01-1-1V8a1 1 0 011-1zM16 9a1 1 0 100 2 1 1 0 000-2zM9 13a1 1 0 011-1h1a1 1 0 110 2v2a1 1 0 11-2 0v-3zM7 11a1 1 0 100-2H4a1 1 0 100 2h3zM17 13a1 1 0 01-1 1h-2a1 1 0 110-2h2a1 1 0 011 1zM16 17a1 1 0 100-2h-3a1 1 0 100 2h3z"
                                />
                            </svg>
                        </div>
                    </div>

                    <h3
                        class="mb-6 text-center font-serif text-2xl font-bold text-amber-900"
                    >
                        QRIS
                    </h3>

                    <div class="text-center">
                        <button
                            @click="showQris = !showQris"
                            class="mb-4 rounded-lg bg-amber-600 px-8 py-3 font-semibold text-white transition hover:bg-amber-700"
                        >
                            {{
                                showQris
                                    ? 'Sembunyikan QR Code'
                                    : 'Tampilkan QR Code'
                            }}
                        </button>

                        <div v-if="showQris" class="mt-6">
                            <img
                                :src="content.qris_image_url"
                                alt="QRIS"
                                class="mx-auto max-w-xs rounded-lg border-4 border-amber-200 shadow-lg"
                            />
                            <p class="mt-4 text-sm text-gray-600">
                                Scan QR Code dengan aplikasi pembayaran Anda
                            </p>
                        </div>
                    </div>
                </div>

                <!-- E-Wallet -->
                <div
                    v-if="
                        content.gopay_number ||
                        content.ovo_number ||
                        content.dana_number
                    "
                    class="rounded-2xl border-4 border-amber-200 bg-gradient-to-br from-amber-50 to-white p-8 shadow-xl"
                >
                    <div class="mb-6 flex items-center justify-center">
                        <div class="rounded-full bg-amber-600 p-4 text-white">
                            <svg
                                class="h-8 w-8"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"
                                />
                            </svg>
                        </div>
                    </div>

                    <h3
                        class="mb-6 text-center font-serif text-2xl font-bold text-amber-900"
                    >
                        E-Wallet
                    </h3>

                    <div class="space-y-4">
                        <div
                            v-if="content.gopay_number"
                            class="flex items-center justify-between rounded-lg border-2 border-amber-200 bg-white p-4"
                        >
                            <div class="flex items-center">
                                <div
                                    class="mr-3 rounded bg-blue-500 px-3 py-1 text-sm font-bold text-white"
                                >
                                    GoPay
                                </div>
                                <span class="font-semibold text-gray-900">{{
                                    content.gopay_number
                                }}</span>
                            </div>
                        </div>

                        <div
                            v-if="content.ovo_number"
                            class="flex items-center justify-between rounded-lg border-2 border-amber-200 bg-white p-4"
                        >
                            <div class="flex items-center">
                                <div
                                    class="mr-3 rounded bg-purple-600 px-3 py-1 text-sm font-bold text-white"
                                >
                                    OVO
                                </div>
                                <span class="font-semibold text-gray-900">{{
                                    content.ovo_number
                                }}</span>
                            </div>
                        </div>

                        <div
                            v-if="content.dana_number"
                            class="flex items-center justify-between rounded-lg border-2 border-amber-200 bg-white p-4"
                        >
                            <div class="flex items-center">
                                <div
                                    class="mr-3 rounded bg-blue-400 px-3 py-1 text-sm font-bold text-white"
                                >
                                    DANA
                                </div>
                                <span class="font-semibold text-gray-900">{{
                                    content.dana_number
                                }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thank You Note -->
            <div class="mt-12 text-center">
                <p class="text-gray-600 italic">
                    Terima kasih atas perhatian dan doa restu Anda 🙏
                </p>
            </div>
        </div>
    </section>
</template>
