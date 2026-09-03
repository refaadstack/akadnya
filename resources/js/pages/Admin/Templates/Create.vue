<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineOptions({
    layout: AdminLayout,
});

interface Section {
    file: string;
    label: string;
    sort_order: number;
    is_required: boolean;
}

interface Ornament {
    file: string;
    label: string;
    position: string;
}

const form = useForm({
    name: '',
    slug: '',
    thumbnail: null as File | null,
    version: '1.0.0',
    is_free: false,
    price: 0,
    is_active: true,
    sections: [
        { file: 'cover.vue', label: 'Cover', sort_order: 1, is_required: true },
    ] as Section[],
    ornaments: [] as Ornament[],
});

const thumbnailPreview = ref<string | null>(null);

const handleThumbnailChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        form.thumbnail = file;
        thumbnailPreview.value = URL.createObjectURL(file);
    }
};

const addSection = () => {
    const nextOrder = form.sections.length + 1;
    form.sections.push({
        file: '',
        label: '',
        sort_order: nextOrder,
        is_required: false,
    });
};

const removeSection = (index: number) => {
    form.sections.splice(index, 1);
    // Reorder
    form.sections.forEach((section, idx) => {
        section.sort_order = idx + 1;
    });
};

const addOrnament = () => {
    form.ornaments.push({
        file: '',
        label: '',
        position: 'top',
    });
};

const removeOrnament = (index: number) => {
    form.ornaments.splice(index, 1);
};

const submit = () => {
    form.post('/admin/templates', {
        preserveScroll: true,
    });
};
</script>

<template>
    <div>
        <Head title="Tambah Template - Admin" />

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
                                Tambah Template Baru
                            </h1>
                            <p class="mt-1 text-gray-600">
                                Buat template undangan baru
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="container mx-auto px-4 py-8">
                <form @submit.prevent="submit" class="max-w-4xl">
                    <!-- Basic Info -->
                    <div class="mb-6 rounded-xl bg-white p-6 shadow-md">
                        <h2 class="mb-4 text-xl font-bold text-gray-900">
                            Informasi Dasar
                        </h2>

                        <div class="space-y-4">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label
                                        class="mb-2 block text-sm font-medium text-gray-700"
                                        >Nama Template *</label
                                    >
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-pink-500"
                                        placeholder="Contoh: Adat Jawa"
                                        required
                                    />
                                    <p
                                        v-if="form.errors.name"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ form.errors.name }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-medium text-gray-700"
                                        >Slug (opsional)</label
                                    >
                                    <input
                                        v-model="form.slug"
                                        type="text"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-pink-500"
                                        placeholder="adat-jawa (auto-generate jika kosong)"
                                    />
                                    <p
                                        v-if="form.errors.slug"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ form.errors.slug }}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                    >Thumbnail</label
                                >
                                <div class="flex items-start space-x-4">
                                    <div
                                        v-if="thumbnailPreview"
                                        class="h-32 w-48 overflow-hidden rounded-lg border-2 border-gray-200"
                                    >
                                        <img
                                            :src="thumbnailPreview"
                                            alt="Preview"
                                            class="h-full w-full object-cover"
                                        />
                                    </div>
                                    <div class="flex-1">
                                        <input
                                            type="file"
                                            @change="handleThumbnailChange"
                                            accept="image/*"
                                            class="hidden"
                                            id="thumbnail-upload"
                                        />
                                        <label
                                            for="thumbnail-upload"
                                            class="inline-flex cursor-pointer items-center rounded-lg border border-gray-300 px-4 py-2 transition hover:bg-gray-50"
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
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                />
                                            </svg>
                                            Pilih Gambar
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-3">
                                <div>
                                    <label
                                        class="mb-2 block text-sm font-medium text-gray-700"
                                        >Version</label
                                    >
                                    <input
                                        v-model="form.version"
                                        type="text"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-pink-500"
                                        placeholder="1.0.0"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-medium text-gray-700"
                                        >Harga</label
                                    >
                                    <input
                                        v-model.number="form.price"
                                        type="number"
                                        :disabled="form.is_free"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-pink-500 disabled:bg-gray-100"
                                        placeholder="0"
                                    />
                                </div>

                                <div class="flex items-center space-x-4 pt-8">
                                    <label class="flex items-center">
                                        <input
                                            v-model="form.is_free"
                                            type="checkbox"
                                            class="mr-2"
                                        />
                                        <span
                                            class="text-sm font-medium text-gray-700"
                                            >Gratis</span
                                        >
                                    </label>
                                    <label class="flex items-center">
                                        <input
                                            v-model="form.is_active"
                                            type="checkbox"
                                            class="mr-2"
                                        />
                                        <span
                                            class="text-sm font-medium text-gray-700"
                                            >Aktif</span
                                        >
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sections -->
                    <div class="mb-6 rounded-xl bg-white p-6 shadow-md">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-xl font-bold text-gray-900">
                                Sections *
                            </h2>
                            <button
                                type="button"
                                @click="addSection"
                                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700"
                            >
                                + Tambah Section
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div
                                v-for="(section, index) in form.sections"
                                :key="index"
                                class="rounded-lg border border-gray-200 p-4"
                            >
                                <div
                                    class="mb-3 flex items-start justify-between"
                                >
                                    <span
                                        class="text-sm font-semibold text-gray-700"
                                        >Section #{{ index + 1 }}</span
                                    >
                                    <button
                                        v-if="form.sections.length > 1"
                                        type="button"
                                        @click="removeSection(index)"
                                        class="text-red-600 hover:text-red-700"
                                    >
                                        <svg
                                            class="h-5 w-5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"
                                            />
                                        </svg>
                                    </button>
                                </div>

                                <div class="grid gap-3 md:grid-cols-3">
                                    <div>
                                        <label
                                            class="mb-1 block text-xs font-medium text-gray-600"
                                            >File *</label
                                        >
                                        <input
                                            v-model="section.file"
                                            type="text"
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-pink-500"
                                            placeholder="cover.vue"
                                            required
                                        />
                                    </div>

                                    <div>
                                        <label
                                            class="mb-1 block text-xs font-medium text-gray-600"
                                            >Label *</label
                                        >
                                        <input
                                            v-model="section.label"
                                            type="text"
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-pink-500"
                                            placeholder="Cover"
                                            required
                                        />
                                    </div>

                                    <div class="flex items-end space-x-2">
                                        <div class="flex-1">
                                            <label
                                                class="mb-1 block text-xs font-medium text-gray-600"
                                                >Order</label
                                            >
                                            <input
                                                v-model.number="
                                                    section.sort_order
                                                "
                                                type="number"
                                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-pink-500"
                                                min="1"
                                                required
                                            />
                                        </div>
                                        <label class="flex items-center pb-2">
                                            <input
                                                v-model="section.is_required"
                                                type="checkbox"
                                                class="mr-1"
                                            />
                                            <span
                                                class="text-xs font-medium text-gray-600"
                                                >Required</span
                                            >
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ornaments -->
                    <div class="mb-6 rounded-xl bg-white p-6 shadow-md">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-xl font-bold text-gray-900">
                                Ornaments (Opsional)
                            </h2>
                            <button
                                type="button"
                                @click="addOrnament"
                                class="rounded-lg bg-purple-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-purple-700"
                            >
                                + Tambah Ornament
                            </button>
                        </div>

                        <div
                            v-if="form.ornaments.length === 0"
                            class="py-8 text-center text-gray-500"
                        >
                            Belum ada ornament. Klik tombol di atas untuk
                            menambahkan.
                        </div>

                        <div class="space-y-4">
                            <div
                                v-for="(ornament, index) in form.ornaments"
                                :key="index"
                                class="rounded-lg border border-gray-200 p-4"
                            >
                                <div
                                    class="mb-3 flex items-start justify-between"
                                >
                                    <span
                                        class="text-sm font-semibold text-gray-700"
                                        >Ornament #{{ index + 1 }}</span
                                    >
                                    <button
                                        type="button"
                                        @click="removeOrnament(index)"
                                        class="text-red-600 hover:text-red-700"
                                    >
                                        <svg
                                            class="h-5 w-5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"
                                            />
                                        </svg>
                                    </button>
                                </div>

                                <div class="grid gap-3 md:grid-cols-3">
                                    <div>
                                        <label
                                            class="mb-1 block text-xs font-medium text-gray-600"
                                            >File *</label
                                        >
                                        <input
                                            v-model="ornament.file"
                                            type="text"
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-pink-500"
                                            placeholder="batik-corner.vue"
                                            required
                                        />
                                    </div>

                                    <div>
                                        <label
                                            class="mb-1 block text-xs font-medium text-gray-600"
                                            >Label *</label
                                        >
                                        <input
                                            v-model="ornament.label"
                                            type="text"
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-pink-500"
                                            placeholder="Batik Corner"
                                            required
                                        />
                                    </div>

                                    <div>
                                        <label
                                            class="mb-1 block text-xs font-medium text-gray-600"
                                            >Position *</label
                                        >
                                        <select
                                            v-model="ornament.position"
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-pink-500"
                                            required
                                        >
                                            <option value="top">Top</option>
                                            <option value="top-left">
                                                Top Left
                                            </option>
                                            <option value="top-right">
                                                Top Right
                                            </option>
                                            <option value="center">
                                                Center
                                            </option>
                                            <option value="bottom">
                                                Bottom
                                            </option>
                                            <option value="bottom-left">
                                                Bottom Left
                                            </option>
                                            <option value="bottom-right">
                                                Bottom Right
                                            </option>
                                            <option value="side">Side</option>
                                            <option value="floating">
                                                Floating
                                            </option>
                                            <option value="background">
                                                Background
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex items-center space-x-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-gradient-to-r from-pink-600 to-purple-600 px-8 py-3 font-semibold text-white transition hover:shadow-lg disabled:opacity-50"
                        >
                            {{
                                form.processing
                                    ? 'Menyimpan...'
                                    : 'Simpan Template'
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
        </div>
    </div>
</template>
