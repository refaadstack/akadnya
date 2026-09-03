<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineOptions({
    layout: AdminLayout,
});

interface Template {
    id: number;
    slug: string;
    name: string;
    thumbnail_url: string | null;
    version: string;
    is_free: boolean;
    price: number;
    is_active: boolean;
    sections_count: number;
    ornaments_count: number;
    synced_at: string | null;
    created_at: string;
}

defineProps<{
    templates: {
        data: Template[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}>();

const deleteTemplate = (id: number) => {
    if (confirm('Yakin ingin menghapus template ini?')) {
        router.delete(`/admin/templates/${id}`);
    }
};

const toggleActive = (id: number) => {
    router.post(`/admin/templates/${id}/toggle-active`);
};

const syncTemplates = () => {
    if (confirm('Sync semua template dari storage/templates/ ke database?')) {
        router.post('/admin/templates/sync');
    }
};
</script>

<template>
    <div>
        <Head title="Kelola Template - Admin" />

        <div class="min-h-screen bg-gray-50">
            <!-- Header -->
            <div class="border-b border-gray-200 bg-white">
                <div class="container mx-auto px-4 py-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">
                                Kelola Template
                            </h1>
                            <p class="mt-1 text-gray-600">
                                Manage wedding invitation templates
                            </p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button
                                @click="syncTemplates"
                                class="flex items-center rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white transition hover:bg-blue-700"
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
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                    />
                                </svg>
                                Sync Templates
                            </button>
                            <Link
                                href="/admin/templates/upload"
                                class="flex items-center rounded-lg bg-gradient-to-r from-pink-600 to-purple-600 px-6 py-3 font-semibold text-white transition hover:shadow-lg"
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
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                                    />
                                </svg>
                                Upload Template
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="container mx-auto px-4 py-8">
                <!-- Flash Messages -->
                <div
                    v-if="$page.props.flash?.success"
                    class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800"
                >
                    {{ $page.props.flash.success }}
                </div>

                <!-- Templates Grid -->
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="template in templates.data"
                        :key="template.id"
                        class="overflow-hidden rounded-xl bg-white shadow-md transition hover:shadow-xl"
                    >
                        <!-- Thumbnail -->
                        <div
                            class="relative h-48 bg-gradient-to-br from-pink-100 to-purple-100"
                        >
                            <img
                                v-if="template.thumbnail_url"
                                :src="template.thumbnail_url"
                                :alt="template.name"
                                class="h-full w-full object-cover"
                            />
                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center"
                            >
                                <svg
                                    class="h-16 w-16 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>
                            </div>

                            <!-- Status Badge -->
                            <div class="absolute top-3 right-3">
                                <span
                                    v-if="template.is_active"
                                    class="rounded-full bg-green-500 px-3 py-1 text-xs font-semibold text-white"
                                >
                                    Aktif
                                </span>
                                <span
                                    v-else
                                    class="rounded-full bg-gray-500 px-3 py-1 text-xs font-semibold text-white"
                                >
                                    Nonaktif
                                </span>
                            </div>

                            <!-- Price Badge -->
                            <div class="absolute top-3 left-3">
                                <span
                                    v-if="template.is_free"
                                    class="rounded-full bg-blue-500 px-3 py-1 text-xs font-semibold text-white"
                                >
                                    Gratis
                                </span>
                                <span
                                    v-else
                                    class="rounded-full bg-purple-600 px-3 py-1 text-xs font-semibold text-white"
                                >
                                    Rp
                                    {{ template.price.toLocaleString('id-ID') }}
                                </span>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-5">
                            <h3 class="mb-2 text-xl font-bold text-gray-900">
                                {{ template.name }}
                            </h3>
                            <p class="mb-4 text-sm text-gray-600">
                                <span class="font-medium">Slug:</span>
                                {{ template.slug }}
                            </p>

                            <!-- Stats -->
                            <div
                                class="mb-4 flex items-center space-x-4 text-sm text-gray-600"
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
                                            d="M4 6h16M4 10h16M4 14h16M4 18h16"
                                        />
                                    </svg>
                                    {{ template.sections_count }} sections
                                </div>
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
                                            d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"
                                        />
                                    </svg>
                                    {{ template.ornaments_count }} ornaments
                                </div>
                            </div>

                            <div class="mb-4 text-xs text-gray-500">
                                <div>Version: {{ template.version }}</div>
                                <div>Created: {{ template.created_at }}</div>
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-col space-y-2">
                                <a
                                    :href="`/templates/${template.slug}/render`"
                                    target="_blank"
                                    class="rounded-lg bg-green-600 px-4 py-2 text-center text-sm font-semibold text-white transition hover:bg-green-700"
                                >
                                    👁️ Preview
                                </a>
                                <div class="flex items-center space-x-2">
                                    <Link
                                        :href="`/admin/templates/${template.id}/edit`"
                                        class="flex-1 rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-semibold text-white transition hover:bg-blue-700"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="toggleActive(template.id)"
                                        class="flex-1 rounded-lg bg-gray-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700"
                                    >
                                        {{
                                            template.is_active
                                                ? 'Nonaktifkan'
                                                : 'Aktifkan'
                                        }}
                                    </button>
                                    <button
                                        @click="deleteTemplate(template.id)"
                                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="templates.data.length === 0"
                    class="py-16 text-center"
                >
                    <svg
                        class="mx-auto mb-4 h-24 w-24 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                        />
                    </svg>
                    <h3 class="mb-2 text-xl font-semibold text-gray-900">
                        Belum ada template
                    </h3>
                    <p class="mb-6 text-gray-600">
                        Mulai dengan menambahkan template pertama Anda
                    </p>
                    <Link
                        href="/admin/templates/create"
                        class="inline-block rounded-lg bg-gradient-to-r from-pink-600 to-purple-600 px-6 py-3 font-semibold text-white transition hover:shadow-lg"
                    >
                        + Tambah Template
                    </Link>
                </div>

                <!-- Pagination -->
                <div
                    v-if="templates.last_page > 1"
                    class="mt-8 flex justify-center"
                >
                    <div class="flex items-center space-x-2">
                        <Link
                            v-for="page in templates.last_page"
                            :key="page"
                            :href="`/admin/templates?page=${page}`"
                            class="rounded-lg px-4 py-2 font-semibold transition"
                            :class="
                                page === templates.current_page
                                    ? 'bg-pink-600 text-white'
                                    : 'bg-white text-gray-700 hover:bg-gray-100'
                            "
                        >
                            {{ page }}
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
