<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Template {
    id: number;
    slug: string;
    name: string;
    thumbnail_url: string | null;
}

interface Props {
    template: Template;
}

const props = defineProps<Props>();

const renderUrl = computed(() => `/templates/${props.template.slug}/render`);

const addToCart = () => {
    router.post(
        '/keranjang',
        {
            item_type: 'template',
            item_id: props.template.id,
        },
        {
            onSuccess: () => {
                router.visit('/keranjang');
            },
        },
    );
};
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <Head :title="`Preview: ${template.name}`" />

        <!-- Header -->
        <header class="sticky top-0 z-50 bg-white shadow-sm">
            <div class="container mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <Link
                            href="/templates"
                            class="flex items-center text-gray-600 hover:text-gray-900"
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
                                    d="M15 19l-7-7 7-7"
                                />
                            </svg>
                            Kembali
                        </Link>
                        <div class="h-6 w-px bg-gray-300"></div>
                        <h1 class="text-xl font-semibold text-gray-900">
                            Preview: {{ template.name }}
                        </h1>
                    </div>

                    <div class="flex items-center space-x-3">
                        <button
                            type="button"
                            @click="addToCart"
                            class="rounded-lg bg-gradient-to-r from-pink-600 to-purple-600 px-6 py-2 font-semibold text-white transition hover:shadow-lg"
                        >
                            Tambah ke Keranjang
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Preview Content -->
        <main class="container mx-auto px-4 py-8">
            <div class="overflow-hidden rounded-lg bg-white shadow-lg">
                <!-- Device Frame (Optional) -->
                <div class="border-b bg-gray-100 p-4">
                    <div class="flex items-center justify-center space-x-2">
                        <div class="flex space-x-1">
                            <div class="h-3 w-3 rounded-full bg-red-500"></div>
                            <div
                                class="h-3 w-3 rounded-full bg-yellow-500"
                            ></div>
                            <div
                                class="h-3 w-3 rounded-full bg-green-500"
                            ></div>
                        </div>
                        <div class="mx-4 max-w-md flex-1">
                            <div
                                class="rounded bg-white px-3 py-1 text-center text-sm text-gray-600"
                            >
                                {{ renderUrl }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Iframe Preview -->
                <div
                    class="relative"
                    style="height: calc(100vh - 250px); min-height: 600px"
                >
                    <iframe
                        :src="renderUrl"
                        class="h-full w-full border-0"
                        title="Template Preview"
                        sandbox="allow-same-origin allow-scripts allow-forms"
                    ></iframe>
                </div>
            </div>

            <!-- Template Info -->
            <div class="mt-6 rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">
                    Tentang Template
                </h2>
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <h3 class="mb-2 text-sm font-medium text-gray-500">
                            Nama Template
                        </h3>
                        <p class="text-gray-900">{{ template.name }}</p>
                    </div>
                    <div>
                        <h3 class="mb-2 text-sm font-medium text-gray-500">
                            Slug
                        </h3>
                        <p class="font-mono text-sm text-gray-900">
                            {{ template.slug }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 border-t pt-6">
                    <p class="mb-4 text-sm text-gray-600">
                        Preview ini menampilkan template dengan data dummy.
                        Setelah Anda memilih template, Anda dapat mengedit
                        konten sesuai keinginan Anda.
                    </p>
                    <button
                        type="button"
                        @click="addToCart"
                        class="inline-block rounded-lg bg-gradient-to-r from-pink-600 to-purple-600 px-8 py-3 font-semibold text-white transition hover:shadow-lg"
                    >
                        Tambah ke Keranjang
                    </button>
                </div>
            </div>
        </main>
    </div>
</template>
