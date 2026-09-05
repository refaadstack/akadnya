<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Html5Qrcode } from 'html5-qrcode';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import PageHeader from '@/components/layout/PageHeader.vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';

defineProps<{
    invitation: {
        id: number;
        subdomain: string;
    };
}>();

const page = usePage();

const scannerId = 'guest-book-scanner';
const scanning = ref(false);
const cameraError = ref<string | null>(null);
const lastResult = ref<{
    code: string;
    status: string;
    message: string;
} | null>(null);

const checkInForm = useForm({
    code: '',
});

let scanner: Html5Qrcode | null = null;

const onScanSuccess = (decodedText: string) => {
    if (checkInForm.processing) {
        return;
    }

    checkInForm.code = decodedText;
    checkInForm.post('/dashboard/guest-book/check-in', {
        preserveScroll: true,
        onSuccess: () => {
            lastResult.value = {
                code: decodedText,
                status: 'success',
                message: page.props.flash?.success ?? 'Check-in berhasil.',
            };
            resumeScanning();
        },
        onError: (error) => {
            const message =
                (error as any)?.response?.data?.message ??
                'Kode tamu tidak ditemukan.';
            lastResult.value = { code: decodedText, status: 'error', message };
            resumeScanning();
        },
    });
};

const startScanner = async () => {
    if (scanner) {
        return;
    }

    cameraError.value = null;
    scanner = new Html5Qrcode(scannerId);

    try {
        await scanner.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: { width: 240, height: 240 } },
            onScanSuccess,
            () => {},
        );
        scanning.value = true;
    } catch {
        cameraError.value =
            'Kamera tidak dapat diakses. Periksa izin kamera browser Anda.';
        scanner = null;
    }
};

const stopScanner = async () => {
    if (!scanner) {
        return;
    }

    try {
        await scanner.stop();
        scanner.clear();
    } catch {
        // Scanner sudah berhenti
    }

    scanner = null;
    scanning.value = false;
};

const resumeScanning = () => {
    lastResult.value = null;
};

onMounted(() => {
    startScanner();
});

onBeforeUnmount(() => {
    stopScanner();
});
</script>

<template>
    <DashboardLayout>
        <Head title="Scan Barcode Tamu" />

        <main class="my-container py-10">
            <div class="mx-auto max-w-xl space-y-6">
                <PageHeader
                    title="Scan Barcode Tamu"
                    description="Arahkan kamera ke barcode tamu untuk check-in otomatis."
                >
                    <template #actions>
                        <Link
                            href="/dashboard/guest-book"
                            class="my-btn-secondary"
                            >Kembali</Link
                        >
                    </template>
                </PageHeader>

                <div
                    v-if="$page.props.flash?.success"
                    class="rounded-lg border border-[#AD7F35]/30 bg-[#AD7F35]/10 px-4 py-3 text-[#5A1B24]"
                >
                    {{ $page.props.flash.success }}
                </div>
                <div
                    v-if="$page.props.flash?.error"
                    class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800"
                >
                    {{ $page.props.flash.error }}
                </div>

                <article class="my-card p-6">
                    <div
                        id="guest-book-scanner"
                        class="overflow-hidden rounded-lg bg-black"
                        :class="
                            scanning
                                ? 'aspect-square'
                                : 'flex aspect-video items-center justify-center'
                        "
                    >
                        <p
                            v-if="!scanning && !cameraError"
                            class="p-6 text-center text-sm text-white/70"
                        >
                            Memuat kamera...
                        </p>
                        <p
                            v-if="cameraError"
                            class="p-6 text-center text-sm text-red-300"
                        >
                            {{ cameraError }}
                        </p>
                    </div>

                    <div class="mt-4 flex items-center justify-between gap-4">
                        <p class="text-sm text-[var(--my-muted)]">
                            {{
                                scanning
                                    ? 'Kamera aktif — scan barcode tamu.'
                                    : 'Kamera tidak aktif.'
                            }}
                        </p>
                        <button
                            type="button"
                            class="my-btn-secondary"
                            :disabled="Boolean(cameraError)"
                            @click="scanning ? stopScanner() : startScanner()"
                        >
                            {{ scanning ? 'Stop Kamera' : 'Nyalakan Kamera' }}
                        </button>
                    </div>
                </article>

                <article
                    v-if="lastResult"
                    class="rounded-xl border p-6"
                    :class="
                        lastResult.status === 'success'
                            ? 'border-[#AD7F35]/30 bg-[#AD7F35]/10'
                            : 'border-red-200 bg-red-50'
                    "
                >
                    <h2
                        class="font-bold"
                        :class="
                            lastResult.status === 'success'
                                ? 'text-[#5A1B24]'
                                : 'text-red-800'
                        "
                    >
                        {{
                            lastResult.status === 'success'
                                ? 'Check-in berhasil'
                                : 'Check-in gagal'
                        }}
                    </h2>
                    <p
                        class="mt-1 text-sm"
                        :class="
                            lastResult.status === 'success'
                                ? 'text-[#9f6b61]'
                                : 'text-red-700'
                        "
                    >
                        {{ lastResult.message }}
                    </p>
                    <p class="mt-2 font-mono text-xs opacity-70">
                        Kode: {{ lastResult.code }}
                    </p>
                </article>

                <article
                    class="rounded-xl border border-[var(--my-border)] bg-white/60 p-6"
                >
                    <h2 class="mb-4 font-bold text-[var(--my-neutral)]">
                        Check-in Manual
                    </h2>
                    <form
                        @submit.prevent="
                            checkInForm.post('/dashboard/guest-book/check-in', {
                                onSuccess: () => checkInForm.reset(),
                            })
                        "
                    >
                        <div class="flex gap-2">
                            <input
                                v-model="checkInForm.code"
                                type="text"
                                placeholder="Kode barcode tamu"
                                class="flex-1 rounded-lg border border-[var(--my-border)] px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-[var(--my-primary)]"
                            />
                            <button
                                type="submit"
                                :disabled="checkInForm.processing"
                                class="my-btn-primary"
                            >
                                {{
                                    checkInForm.processing
                                        ? 'Memproses...'
                                        : 'Check-in'
                                }}
                            </button>
                        </div>
                        <p
                            v-if="checkInForm.errors.code"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ checkInForm.errors.code }}
                        </p>
                    </form>
                </article>
            </div>
        </main>
    </DashboardLayout>
</template>
