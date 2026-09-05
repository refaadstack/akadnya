<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Field, TextInput } from '@/components/form';
import DashboardLayout from '@/layouts/DashboardLayout.vue';

interface Invitation {
    id: number;
    subdomain: string;
    custom_domain: string | null;
    status: string;
    view_count: number;
    public_url: string;
    is_published: boolean;
}

const props = defineProps<{
    invitation: Invitation;
    has_custom_domain: boolean;
    app_domain: string;
}>();

const subdomainForm = useForm({
    subdomain: props.invitation.subdomain,
});

const customDomainForm = useForm({
    custom_domain: props.invitation.custom_domain || '',
});

const showCopied = ref(false);
const showSubdomainGuide = ref(false);
const showCustomDomainGuide = ref(false);

const copyUrl = () => {
    navigator.clipboard.writeText(props.invitation.public_url);
    showCopied.value = true;
    setTimeout(() => {
        showCopied.value = false;
    }, 2000);
};

const generateSubdomain = async () => {
    try {
        const xsrfToken = decodeURIComponent(
            document.cookie
                .split('; ')
                .find((row) => row.startsWith('XSRF-TOKEN='))
                ?.split('=')[1] ?? '',
        );
        const response = await fetch('/dashboard/settings/generate-subdomain', {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': xsrfToken,
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const data = await response.json();
        subdomainForm.subdomain = data.subdomain;
    } catch (error) {
        console.error('Failed to generate subdomain:', error);
    }
};

const updateSubdomain = () => {
    subdomainForm.post('/dashboard/settings/subdomain', {
        preserveScroll: true,
    });
};

const updateCustomDomain = () => {
    customDomainForm.post('/dashboard/settings/custom-domain', {
        preserveScroll: true,
    });
};

const publish = () => {
    router.post('/dashboard/settings/publish');
};

const unpublish = () => {
    if (
        confirm(
            'Yakin ingin unpublish undangan? Tamu tidak akan bisa mengakses undangan.',
        )
    ) {
        router.post('/dashboard/settings/unpublish');
    }
};
</script>

<template>
    <DashboardLayout>
        <Head title="Pengaturan Undangan" />

        <!-- Content -->
        <div class="container mx-auto px-4 py-8">
            <!-- Flash Messages -->
            <div
                v-if="$page.props.flash?.success"
                class="mb-6 flex items-center rounded-lg border border-[#AD7F35]/30 bg-[#AD7F35]/10 px-4 py-3 text-[#5A1B24]"
            >
                <svg
                    class="mr-2 h-5 w-5"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                >
                    <path
                        fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd"
                    />
                </svg>
                {{ $page.props.flash.success }}
            </div>

            <div
                v-if="$page.props.flash?.error"
                class="mb-6 flex items-center rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800"
            >
                <svg
                    class="mr-2 h-5 w-5"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                >
                    <path
                        fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd"
                    />
                </svg>
                {{ $page.props.flash.error }}
            </div>

            <div class="max-w-3xl">
                <!-- Status & Publish -->
                <div class="mb-6 rounded-xl bg-white p-6 shadow-md">
                    <h2 class="mb-4 text-xl font-bold text-gray-900">
                        Status Publikasi
                    </h2>

                    <div
                        class="mb-4 flex items-center justify-between rounded-lg bg-gray-50 p-4"
                    >
                        <div class="flex items-center space-x-3">
                            <div
                                class="h-3 w-3 rounded-full"
                                :class="
                                    invitation.is_published
                                        ? 'bg-[#AD7F35]'
                                        : 'bg-gray-400'
                                "
                            ></div>
                            <div>
                                <p class="font-semibold text-gray-900">
                                    {{
                                        invitation.is_published
                                            ? 'Dipublikasikan'
                                            : 'Draft'
                                    }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    {{
                                        invitation.is_published
                                            ? 'Undangan dapat diakses publik'
                                            : 'Undangan belum dipublikasikan'
                                    }}
                                </p>
                            </div>
                        </div>

                        <button
                            v-if="!invitation.is_published"
                            @click="publish"
                            class="rounded-lg bg-[#AD7F35] px-6 py-2 font-semibold text-white transition hover:bg-[#5A1B24]"
                        >
                            Publikasikan
                        </button>
                        <button
                            v-else
                            @click="unpublish"
                            class="rounded-lg bg-gray-600 px-6 py-2 font-semibold text-white transition hover:bg-gray-700"
                        >
                            Unpublish
                        </button>
                    </div>

                    <div v-if="invitation.is_published" class="border-t pt-4">
                        <p class="mb-2 text-sm text-gray-600">URL Undangan:</p>
                        <div class="flex items-center space-x-2">
                            <input
                                type="text"
                                :value="invitation.public_url"
                                readonly
                                class="flex-1 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700"
                            />
                            <button
                                @click="copyUrl"
                                class="flex items-center rounded-lg bg-[#5A1B24] px-4 py-2 font-semibold text-white transition hover:bg-[#5A1B24]/80"
                            >
                                <svg
                                    class="mr-1 h-5 w-5"
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
                                {{ showCopied ? 'Tersalin!' : 'Salin' }}
                            </button>
                        </div>

                        <div
                            class="mt-4 flex items-center space-x-4 text-sm text-gray-600"
                        >
                            <div class="flex items-center">
                                <svg
                                    class="mr-1 h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                    />
                                </svg>
                                {{ invitation.view_count }} views
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subdomain -->
                <div class="mb-6 rounded-xl bg-white p-6 shadow-md">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-900">
                            Subdomain
                        </h2>
                        <button
                            @click="showSubdomainGuide = !showSubdomainGuide"
                            class="flex items-center text-sm font-medium text-[#5A1B24] hover:text-[#5A1B24]"
                        >
                            <svg
                                class="mr-1 h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            {{ showSubdomainGuide ? 'Sembunyikan' : 'Panduan' }}
                        </button>
                    </div>

                    <!-- Guide Section -->
                    <div
                        v-if="showSubdomainGuide"
                        class="mb-6 rounded-lg border border-[#5A1B24]/30 bg-gradient-to-r from-[#5A1B24]/10 to-[#5A1B24]/10 p-5"
                    >
                        <h3
                            class="mb-3 flex items-center font-bold text-[#5A1B24]"
                        >
                            <svg
                                class="mr-2 h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                                />
                            </svg>
                            Panduan Subdomain
                        </h3>

                        <div class="space-y-3 text-sm text-[#5A1B24]">
                            <div>
                                <p class="mb-1 font-semibold">
                                    📌 Apa itu Subdomain?
                                </p>
                                <p class="text-[#5A1B24]">
                                    Subdomain adalah alamat unik untuk undangan
                                    Anda. Contoh:
                                    <code
                                        class="rounded bg-[#5A1B24]/10 px-2 py-0.5"
                                        >siti-budi-2024.{{ app_domain }}</code
                                    >
                                </p>
                            </div>

                            <div>
                                <p class="mb-1 font-semibold">
                                    🔄 Kenapa Auto-Generate?
                                </p>
                                <p class="text-[#5A1B24]">
                                    Sistem otomatis membuat subdomain dari nama
                                    mempelai atau nama Anda untuk kemudahan.
                                    Tapi tenang, Anda bisa mengubahnya kapan
                                    saja!
                                </p>
                            </div>

                            <div>
                                <p class="mb-1 font-semibold">
                                    ✅ Aturan Subdomain:
                                </p>
                                <ul
                                    class="ml-2 list-inside list-disc space-y-1 text-[#5A1B24]"
                                >
                                    <li>
                                        Minimal 3 karakter, maksimal 50 karakter
                                    </li>
                                    <li>
                                        Hanya huruf kecil (a-z), angka (0-9),
                                        dan tanda hubung (-)
                                    </li>
                                    <li>
                                        Tidak boleh spasi atau karakter khusus
                                        lain
                                    </li>
                                    <li>
                                        Harus unik (belum dipakai user lain)
                                    </li>
                                </ul>
                            </div>

                            <div>
                                <p class="mb-1 font-semibold">
                                    💡 Tips Memilih Subdomain:
                                </p>
                                <ul
                                    class="ml-2 list-inside list-disc space-y-1 text-[#5A1B24]"
                                >
                                    <li>
                                        <strong>Mudah diingat:</strong>
                                        <code
                                            class="rounded bg-[#5A1B24]/10 px-1"
                                            >siti-dan-budi</code
                                        >
                                    </li>
                                    <li>
                                        <strong>Tambah tahun:</strong>
                                        <code
                                            class="rounded bg-[#5A1B24]/10 px-1"
                                            >pernikahan-kami-2024</code
                                        >
                                    </li>
                                    <li>
                                        <strong>Singkat & jelas:</strong>
                                        <code
                                            class="rounded bg-[#5A1B24]/10 px-1"
                                            >wedding-jakarta</code
                                        >
                                    </li>
                                </ul>
                            </div>

                            <div
                                class="rounded-lg border border-[#5A1B24]/30 bg-white p-3"
                            >
                                <p class="mb-1 font-semibold text-[#9f6b61]">
                                    ✅ Contoh Valid:
                                </p>
                                <div class="mb-2 flex flex-wrap gap-2">
                                    <code
                                        class="rounded bg-[#AD7F35]/10 px-2 py-1 text-xs text-[#9f6b61]"
                                        >siti-budi-2024</code
                                    >
                                    <code
                                        class="rounded bg-[#AD7F35]/10 px-2 py-1 text-xs text-[#9f6b61]"
                                        >pernikahan-kami</code
                                    >
                                    <code
                                        class="rounded bg-[#AD7F35]/10 px-2 py-1 text-xs text-[#9f6b61]"
                                        >wedding-jakarta</code
                                    >
                                </div>
                                <p class="mb-1 font-semibold text-red-700">
                                    ❌ Contoh Tidak Valid:
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    <code
                                        class="rounded bg-red-50 px-2 py-1 text-xs text-red-700 line-through"
                                        >Siti Budi</code
                                    >
                                    <code
                                        class="rounded bg-red-50 px-2 py-1 text-xs text-red-700 line-through"
                                        >siti_budi</code
                                    >
                                    <code
                                        class="rounded bg-red-50 px-2 py-1 text-xs text-red-700 line-through"
                                        >sb</code
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <form @submit.prevent="updateSubdomain">
                        <div class="space-y-4">
                            <Field
                                label="Subdomain Akadnya.com"
                                :error="subdomainForm.errors.subdomain"
                                hint="Hanya huruf kecil, angka, dan tanda hubung (-). Min 3 karakter."
                            >
                                <div class="flex items-center space-x-2">
                                    <div class="flex-1">
                                        <TextInput
                                            v-model="subdomainForm.subdomain"
                                            placeholder="nama-undangan"
                                            required
                                        />
                                    </div>
                                    <span class="text-gray-600"
                                        >.{{ app_domain }}</span
                                    >
                                </div>
                            </Field>

                            <div class="flex items-center space-x-3">
                                <button
                                    type="submit"
                                    :disabled="subdomainForm.processing"
                                    class="rounded-lg bg-[#AD7F35] px-6 py-2 font-semibold text-white transition hover:bg-[#5A1B24] disabled:opacity-50"
                                >
                                    {{
                                        subdomainForm.processing
                                            ? 'Menyimpan...'
                                            : 'Simpan Subdomain'
                                    }}
                                </button>

                                <button
                                    type="button"
                                    @click="generateSubdomain"
                                    class="rounded-lg bg-gray-600 px-6 py-2 font-semibold text-white transition hover:bg-gray-700"
                                >
                                    Generate Random
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Custom Domain -->
                <div class="mb-6 rounded-xl bg-white p-6 shadow-md">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center">
                            <div>
                                <h2 class="text-xl font-bold text-gray-900">
                                    Custom Domain
                                </h2>
                                <p class="mt-1 text-sm text-gray-600">
                                    Gunakan domain sendiri (opsional)
                                </p>
                            </div>
                            <span
                                class="ml-3 rounded-full bg-[#AD7F35]/10 px-3 py-1 text-xs font-semibold text-[#9f6b61]"
                            >
                                Premium
                            </span>
                        </div>
                        <button
                            @click="
                                showCustomDomainGuide = !showCustomDomainGuide
                            "
                            class="flex items-center text-sm font-medium text-[#AD7F35] hover:text-[#AD7F35]"
                        >
                            <svg
                                class="mr-1 h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            {{
                                showCustomDomainGuide
                                    ? 'Sembunyikan'
                                    : 'Panduan'
                            }}
                        </button>
                    </div>

                    <!-- Guide Section -->
                    <div
                        v-if="showCustomDomainGuide"
                        class="mb-6 rounded-lg border border-[#AD7F35]/30 bg-gradient-to-r from-[#AD7F35]/10 to-[#D8BA82]/10 p-5"
                    >
                        <h3
                            class="mb-3 flex items-center font-bold text-[#5A1B24]"
                        >
                            <svg
                                class="mr-2 h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                                />
                            </svg>
                            Panduan Custom Domain
                        </h3>

                        <div class="space-y-3 text-sm text-[#5A1B24]">
                            <div>
                                <p class="mb-1 font-semibold">
                                    🌐 Apa itu Custom Domain?
                                </p>
                                <p class="text-[#5A1B24]">
                                    Custom domain memungkinkan Anda menggunakan
                                    domain sendiri untuk undangan. Contoh:
                                    <code
                                        class="rounded bg-[#AD7F35]/10 px-2 py-0.5"
                                        >undangan.example.com</code
                                    >
                                    atau
                                    <code
                                        class="rounded bg-[#AD7F35]/10 px-2 py-0.5"
                                        >wedding.mydomain.id</code
                                    >
                                </p>
                            </div>

                            <div>
                                <p class="mb-1 font-semibold">
                                    ⚙️ Cara Setup Custom Domain:
                                </p>
                                <ol
                                    class="ml-2 list-inside list-decimal space-y-2 text-[#5A1B24]"
                                >
                                    <li>
                                        <strong>Beli domain</strong> dari
                                        provider seperti Niagahoster, Dewaweb,
                                        atau Namecheap
                                    </li>
                                    <li>
                                        <strong>Login ke DNS Management</strong>
                                        di provider domain Anda
                                    </li>
                                    <li>
                                        <strong>Buat CNAME record:</strong>
                                        <div
                                            class="mt-1 rounded border border-[#AD7F35]/30 bg-white p-2"
                                        >
                                            <p class="text-xs">
                                                Type:
                                                <code
                                                    class="rounded bg-[#AD7F35]/10 px-1"
                                                    >CNAME</code
                                                >
                                            </p>
                                            <p class="text-xs">
                                                Name:
                                                <code
                                                    class="rounded bg-[#AD7F35]/10 px-1"
                                                    >undangan</code
                                                >
                                                (atau subdomain lain)
                                            </p>
                                            <p class="text-xs">
                                                Value:
                                                <code
                                                    class="rounded bg-[#AD7F35]/10 px-1"
                                                    >{{ app_domain }}</code
                                                >
                                            </p>
                                        </div>
                                    </li>
                                    <li>
                                        <strong>Tunggu propagasi DNS</strong>
                                        (5-30 menit, kadang sampai 24 jam)
                                    </li>
                                    <li>
                                        <strong>Masukkan domain</strong> di form
                                        ini dan klik Simpan
                                    </li>
                                    <li>
                                        <strong>Verifikasi</strong> dengan
                                        membuka domain Anda di browser
                                    </li>
                                </ol>
                            </div>

                            <div>
                                <p class="mb-1 font-semibold">
                                    📋 Format Domain yang Valid:
                                </p>
                                <ul
                                    class="ml-2 list-inside list-disc space-y-1 text-[#5A1B24]"
                                >
                                    <li>
                                        <code
                                            class="rounded bg-[#AD7F35]/10 px-1"
                                            >undangan.example.com</code
                                        >
                                    </li>
                                    <li>
                                        <code
                                            class="rounded bg-[#AD7F35]/10 px-1"
                                            >wedding.mydomain.id</code
                                        >
                                    </li>
                                    <li>
                                        <code
                                            class="rounded bg-[#AD7F35]/10 px-1"
                                            >pernikahan.sitibudi.com</code
                                        >
                                    </li>
                                </ul>
                            </div>

                            <div
                                class="rounded-lg border border-yellow-200 bg-yellow-50 p-3"
                            >
                                <p class="mb-1 font-semibold text-yellow-800">
                                    ⚠️ Catatan Penting:
                                </p>
                                <ul
                                    class="ml-2 list-inside list-disc space-y-1 text-xs text-yellow-700"
                                >
                                    <li>
                                        Custom domain memerlukan paket Premium
                                    </li>
                                    <li>
                                        Pastikan domain sudah aktif dan DNS
                                        sudah propagasi
                                    </li>
                                    <li>
                                        Jika ada masalah, hubungi support kami
                                    </li>
                                </ul>
                            </div>

                            <div>
                                <p class="mb-1 font-semibold">
                                    🔍 Troubleshooting:
                                </p>
                                <div class="space-y-2 text-[#5A1B24]">
                                    <div
                                        class="rounded border border-[#AD7F35]/30 bg-white p-2"
                                    >
                                        <p class="text-xs font-medium">
                                            ❓ Domain tidak bisa diakses?
                                        </p>
                                        <p class="text-xs">
                                            → Tunggu propagasi DNS (bisa sampai
                                            24 jam)
                                        </p>
                                    </div>
                                    <div
                                        class="rounded border border-[#AD7F35]/30 bg-white p-2"
                                    >
                                        <p class="text-xs font-medium">
                                            ❓ Error "Domain sudah digunakan"?
                                        </p>
                                        <p class="text-xs">
                                            → Domain sudah dipakai user lain,
                                            gunakan subdomain berbeda
                                        </p>
                                    </div>
                                    <div
                                        class="rounded border border-[#AD7F35]/30 bg-white p-2"
                                    >
                                        <p class="text-xs font-medium">
                                            ❓ Tidak punya domain?
                                        </p>
                                        <p class="text-xs">
                                            → Gunakan subdomain gratis
                                            Akadnya.com saja, sudah cukup!
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form @submit.prevent="updateCustomDomain">
                        <div class="space-y-4">
                            <Field
                                label="Domain Kustom"
                                :error="customDomainForm.errors.custom_domain"
                                hint="Contoh: undangan.example.com atau wedding.mydomain.id"
                            >
                                <TextInput
                                    v-model="customDomainForm.custom_domain"
                                    :disabled="
                                        !props.has_custom_domain &&
                                        !props.invitation.custom_domain
                                    "
                                    placeholder="undangan.example.com"
                                />
                            </Field>

                            <div
                                v-if="
                                    props.has_custom_domain ||
                                    props.invitation.custom_domain
                                "
                                class="rounded-lg border border-[#5A1B24]/30 bg-[#5A1B24]/10 p-4"
                            >
                                <h4 class="mb-2 font-semibold text-[#5A1B24]">
                                    Cara Setup Custom Domain:
                                </h4>
                                <ol
                                    class="list-inside list-decimal space-y-1 text-sm text-[#5A1B24]"
                                >
                                    <li>
                                        Buat CNAME record di DNS provider Anda
                                    </li>
                                    <li>
                                        Arahkan ke:
                                        <code
                                            class="rounded bg-[#5A1B24]/10 px-2 py-0.5"
                                            >{{ app_domain }}</code
                                        >
                                    </li>
                                    <li>Tunggu propagasi DNS (5-30 menit)</li>
                                    <li>Simpan domain di form ini</li>
                                </ol>
                            </div>

                            <div
                                v-else
                                class="rounded-lg border border-gray-200 bg-gray-50 p-4"
                            >
                                <h4 class="mb-2 font-semibold text-gray-800">
                                    🔒 Fitur Custom Domain
                                </h4>
                                <p class="mb-3 text-sm text-gray-600">
                                    Gunakan domain pribadi Anda untuk undangan.
                                    Fitur ini membutuhkan add-on Custom Domain.
                                </p>
                                <Link
                                    href="/produk"
                                    class="inline-block rounded-lg bg-[#AD7F35] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#9f6b61]"
                                >
                                    Beli Add-on Custom Domain
                                </Link>
                            </div>

                            <button
                                type="submit"
                                :disabled="
                                    customDomainForm.processing ||
                                    (!props.has_custom_domain &&
                                        !props.invitation.custom_domain)
                                "
                                class="rounded-lg bg-[#AD7F35] px-6 py-2 font-semibold text-white transition hover:bg-[#9f6b61] disabled:opacity-50"
                            >
                                {{
                                    customDomainForm.processing
                                        ? 'Menyimpan...'
                                        : 'Simpan Custom Domain'
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
