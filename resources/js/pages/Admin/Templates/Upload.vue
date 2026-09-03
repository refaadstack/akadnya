<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineOptions({
    layout: AdminLayout,
});

const form = useForm({
    zip_file: null as File | null,
});

const fileName = ref<string>('');

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        form.zip_file = file;
        fileName.value = file.name;
    }
};

const submit = () => {
    form.post('/admin/templates/upload-zip', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            fileName.value = '';
        },
    });
};
</script>

<template>
    <div>
        <Head title="Upload Template - Admin" />

        <div class="min-h-screen bg-gray-50">
            <!-- Header -->
            <div class="border-b border-gray-200 bg-white">
                <div class="container mx-auto px-4 py-6">
                    <div class="flex items-center space-x-4">
                        <Link
                            href="/admin/templates"
                            class="text-gray-600 hover:text-gray-900"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 19l-7-7 7-7"
                                />
                            </svg>
                        </Link>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">
                                Upload Template
                            </h1>
                            <p class="mt-1 text-gray-600">
                                Upload template undangan dalam format ZIP
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="container mx-auto px-4 py-8">
                <div class="mx-auto max-w-4xl">
                    <!-- Flash Messages -->
                    <div
                        v-if="$page.props.flash?.success"
                        class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800"
                    >
                        {{ $page.props.flash.success }}
                    </div>
                    <div
                        v-if="$page.props.errors?.error"
                        class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800"
                    >
                        {{ $page.props.errors.error }}
                    </div>

                    <!-- Upload Form -->
                    <div class="mb-6 rounded-xl bg-white p-8 shadow-md">
                        <h2 class="mb-6 text-2xl font-bold text-gray-900">
                            Upload File ZIP
                        </h2>

                        <form @submit.prevent="submit">
                            <!-- File Upload Area -->
                            <div
                                class="rounded-xl border-2 border-dashed border-gray-300 p-12 text-center transition hover:border-pink-500"
                            >
                                <input
                                    type="file"
                                    @change="handleFileChange"
                                    accept=".zip"
                                    class="hidden"
                                    id="zip-upload"
                                    required
                                />

                                <label for="zip-upload" class="cursor-pointer">
                                    <div class="flex flex-col items-center">
                                        <svg
                                            class="mb-4 h-16 w-16 text-gray-400"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                                            />
                                        </svg>

                                        <p
                                            v-if="!fileName"
                                            class="mb-2 text-lg font-semibold text-gray-700"
                                        >
                                            Klik untuk pilih file ZIP
                                        </p>
                                        <p
                                            v-else
                                            class="mb-2 text-lg font-semibold text-pink-600"
                                        >
                                            📦 {{ fileName }}
                                        </p>

                                        <p class="text-sm text-gray-500">
                                            Maksimal ukuran file: 50MB
                                        </p>
                                    </div>
                                </label>
                            </div>

                            <!-- Submit Button -->
                            <div class="mt-6 flex items-center space-x-4">
                                <button
                                    type="submit"
                                    :disabled="
                                        form.processing || !form.zip_file
                                    "
                                    class="rounded-lg bg-gradient-to-r from-pink-600 to-purple-600 px-8 py-3 font-semibold text-white transition hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {{
                                        form.processing
                                            ? 'Mengupload...'
                                            : 'Upload & Sync Template'
                                    }}
                                </button>

                                <Link
                                    href="/admin/templates"
                                    class="font-medium text-gray-600 hover:text-gray-800"
                                >
                                    Batal
                                </Link>
                            </div>
                        </form>
                    </div>

                    <!-- Instructions -->
                    <div
                        class="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-6"
                    >
                        <h3
                            class="mb-4 flex items-center text-lg font-bold text-blue-900"
                        >
                            <svg
                                class="mr-2 h-6 w-6"
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
                            Cara Upload Template
                        </h3>

                        <ol class="space-y-3 text-sm text-blue-900">
                            <li class="flex items-start">
                                <span class="mr-2 font-bold">1.</span>
                                <span
                                    >Siapkan folder template dengan struktur
                                    yang benar (lihat panduan di bawah)</span
                                >
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2 font-bold">2.</span>
                                <span
                                    >Compress folder template menjadi file
                                    ZIP</span
                                >
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2 font-bold">3.</span>
                                <span
                                    >Upload file ZIP menggunakan form di
                                    atas</span
                                >
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2 font-bold">4.</span>
                                <span
                                    >System akan otomatis extract, validasi, dan
                                    sync ke database</span
                                >
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2 font-bold">5.</span>
                                <span>Template siap digunakan!</span>
                            </li>
                        </ol>
                    </div>

                    <!-- Template Structure Guide -->
                    <div class="rounded-xl bg-white p-6 shadow-md">
                        <h3 class="mb-4 text-lg font-bold text-gray-900">
                            📁 Struktur Template yang Benar
                        </h3>

                        <div
                            class="mb-4 overflow-x-auto rounded-lg bg-gray-900 p-4 font-mono text-sm text-green-400"
                        >
                            <pre>
adat-jawa/                    ← Folder utama (nama bebas)
├── template.json             ← WAJIB: Metadata template
├── thumbnail.jpg             ← WAJIB: Preview thumbnail
├── assets/
│   ├── style.css            ← WAJIB: CSS template
│   └── script.js            ← WAJIB: JavaScript template
├── sections/                 ← WAJIB: Folder sections
│   ├── cover.html
│   ├── opening.html
│   ├── bride-groom.html
│   ├── event-details.html
│   ├── love-story.html
│   ├── gallery.html
│   ├── gift.html
│   ├── rsvp.html
│   ├── wishes.html
│   └── closing.html
└── ornaments/                ← OPSIONAL: Folder ornaments
    ├── ornament1.html
    ├── ornament2.html
    └── ornament3.html</pre
                            >
                        </div>

                        <h4 class="mb-2 font-bold text-gray-900">
                            Contoh template.json:
                        </h4>
                        <div
                            class="overflow-x-auto rounded-lg bg-gray-900 p-4 font-mono text-xs text-yellow-300"
                        >
                            <pre>
{
  "name": "Adat Jawa",
  "slug": "adat-jawa",
  "version": "1.0.0",
  "thumbnail": "thumbnail.jpg",
  "is_free": false,
  "price": 149000,
  "sections": [
    {
      "file": "cover.html",
      "label": "Cover",
      "sort_order": 1,
      "is_required": true
    },
    {
      "file": "opening.html",
      "label": "Opening",
      "sort_order": 2,
      "is_required": false
    }
  ],
  "ornaments": [
    {
      "file": "batik-corner.html",
      "label": "Batik Corner",
      "position": "top-left",
      "default_active": true
    }
  ]
}</pre
                            >
                        </div>

                        <div
                            class="mt-4 rounded-lg border border-yellow-200 bg-yellow-50 p-4"
                        >
                            <p class="text-sm text-yellow-900">
                                <strong>💡 Tips:</strong> Lihat contoh template
                                lengkap di folder
                                <code class="rounded bg-yellow-200 px-2 py-1"
                                    >storage/templates/adat-jawa/</code
                                >
                                untuk referensi struktur yang benar.
                            </p>
                        </div>

                        <div class="mt-4">
                            <Link
                                href="/admin/templates"
                                class="inline-flex items-center font-semibold text-pink-600 hover:text-pink-700"
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
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                    />
                                </svg>
                                Baca Dokumentasi Lengkap (TEMPLATE_GUIDE.md)
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
