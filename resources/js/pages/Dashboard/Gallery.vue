<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Field, TextInput } from '@/components/form';
import DashboardLayout from '@/layouts/DashboardLayout.vue';

interface Photo {
    id: number;
    image_url: string;
    caption: string | null;
    sort_order: number;
}

const props = defineProps<{
    gallery: Photo[];
}>();

const uploadForm = useForm({
    file: null as File | null,
    caption: '',
});

const editingCaption = ref<number | null>(null);
const editCaptionForm = useForm({
    caption: '',
});

const draggedItem = ref<number | null>(null);

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        uploadForm.file = file;
    }
};

const uploadPhoto = () => {
    if (!uploadForm.file) {
        return;
    }

    const formData = new FormData();
    formData.append('file', uploadForm.file);
    formData.append('caption', uploadForm.caption);

    uploadForm.post('/dashboard/gallery', {
        preserveScroll: true,
        onSuccess: () => {
            uploadForm.reset();
            const fileInput = document.getElementById(
                'photo-upload',
            ) as HTMLInputElement;

            if (fileInput) {
                fileInput.value = '';
            }
        },
    });
};

const startEditCaption = (photo: Photo) => {
    editingCaption.value = photo.id;
    editCaptionForm.caption = photo.caption || '';
};

const saveCaption = (photoId: number) => {
    editCaptionForm.post(`/dashboard/gallery/${photoId}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingCaption.value = null;
        },
    });
};

const deletePhoto = (photoId: number) => {
    if (confirm('Yakin ingin menghapus foto ini?')) {
        router.delete(`/dashboard/gallery/${photoId}`);
    }
};

const onDragStart = (index: number) => {
    draggedItem.value = index;
};

const onDragOver = (event: DragEvent) => {
    event.preventDefault();
};

const onDrop = (event: DragEvent, dropIndex: number) => {
    event.preventDefault();

    if (draggedItem.value === null) {
        return;
    }

    const items = [...props.gallery];
    const draggedPhoto = items[draggedItem.value];

    items.splice(draggedItem.value, 1);
    items.splice(dropIndex, 0, draggedPhoto);

    // Update sort_order
    const reorderedPhotos = items.map((photo, index) => ({
        id: photo.id,
        sort_order: index,
    }));

    router.post(
        '/dashboard/gallery/reorder',
        {
            photos: reorderedPhotos,
        },
        {
            preserveScroll: true,
        },
    );

    draggedItem.value = null;
};
</script>

<template>
    <DashboardLayout>
        <Head title="Galeri Foto" />

        <!-- Content -->
        <main class="my-container py-10">
            <PageHeader
                title="Galeri Foto"
                description="Upload dan atur urutan foto galeri undangan."
            />
            <!-- Flash Messages -->
            <div
                v-if="$page.props.flash?.success"
                class="mb-6 rounded-lg border border-[#AD7F35]/30 bg-[#AD7F35]/10 px-4 py-3 text-[#5A1B24]"
            >
                {{ $page.props.flash.success }}
            </div>

            <div
                v-if="$page.props.flash?.error"
                class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800"
            >
                {{ $page.props.flash.error }}
            </div>

            <!-- Upload Section -->
            <div class="my-card mb-6 p-6">
                <h2 class="mb-4 text-xl font-bold text-gray-900">
                    Upload Foto Baru
                </h2>

                <form @submit.prevent="uploadPhoto">
                    <div class="space-y-4">
                        <Field
                            label="Pilih Foto"
                            input-id="photo-upload"
                            hint="Format: JPG, PNG, WebP. Maksimal 5MB"
                        >
                            <input
                                type="file"
                                @change="handleFileChange"
                                accept="image/*"
                                id="photo-upload"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 transition focus:border-transparent focus:ring-2 focus:ring-[#AD7F35]"
                                required
                            />
                        </Field>

                        <Field label="Caption (Opsional)">
                            <TextInput
                                v-model="uploadForm.caption"
                                placeholder="Deskripsi foto..."
                            />
                        </Field>

                        <button
                            type="submit"
                            :disabled="
                                uploadForm.processing || !uploadForm.file
                            "
                            class="rounded-lg bg-[#AD7F35] px-6 py-2 font-semibold text-white transition hover:bg-[#5A1B24] disabled:opacity-50"
                        >
                            {{
                                uploadForm.processing
                                    ? 'Uploading...'
                                    : 'Upload Foto'
                            }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Gallery Grid -->
            <div class="my-card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900">
                        Galeri ({{ gallery.length }} foto)
                    </h2>
                    <p class="text-sm text-gray-600">
                        Drag & drop untuk mengubah urutan
                    </p>
                </div>

                <!-- Empty State -->
                <div v-if="gallery.length === 0" class="py-16 text-center">
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
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                        />
                    </svg>
                    <h3 class="mb-2 text-xl font-semibold text-gray-900">
                        Belum ada foto
                    </h3>
                    <p class="text-gray-600">
                        Upload foto pertama Anda untuk galeri undangan
                    </p>
                </div>

                <!-- Photos Grid -->
                <div
                    v-else
                    class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4"
                >
                    <div
                        v-for="(photo, index) in gallery"
                        :key="photo.id"
                        draggable="true"
                        @dragstart="onDragStart(index)"
                        @dragover="onDragOver"
                        @drop="onDrop($event, index)"
                        class="group relative aspect-square cursor-move overflow-hidden rounded-lg bg-gray-100"
                    >
                        <!-- Image -->
                        <img
                            :src="photo.image_url"
                            :alt="photo.caption || 'Gallery photo'"
                            class="h-full w-full object-cover"
                        />

                        <!-- Overlay -->
                        <div
                            class="bg-opacity-0 group-hover:bg-opacity-50 absolute inset-0 flex items-center justify-center bg-black transition"
                        >
                            <div
                                class="space-x-2 opacity-0 transition group-hover:opacity-100"
                            >
                                <button
                                    @click="startEditCaption(photo)"
                                    class="rounded-lg bg-[#5A1B24] p-2 text-white transition hover:bg-[#5A1B24]/80"
                                    title="Edit caption"
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
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                        />
                                    </svg>
                                </button>
                                <button
                                    @click="deletePhoto(photo.id)"
                                    class="rounded-lg bg-red-600 p-2 text-white transition hover:bg-red-700"
                                    title="Hapus foto"
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
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Caption -->
                        <div
                            v-if="photo.caption"
                            class="absolute right-0 bottom-0 left-0 bg-gradient-to-t from-black to-transparent p-3"
                        >
                            <p class="truncate text-sm text-white">
                                {{ photo.caption }}
                            </p>
                        </div>

                        <!-- Sort Order Badge -->
                        <div
                            class="bg-opacity-75 absolute top-2 left-2 rounded bg-gray-900 px-2 py-1 text-xs font-semibold text-white"
                        >
                            #{{ index + 1 }}
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Edit Caption Modal -->
        <div
            v-if="editingCaption !== null"
            class="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center bg-black p-4"
            @click.self="editingCaption = null"
        >
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-xl font-bold text-gray-900">
                    Edit Caption
                </h3>

                <form @submit.prevent="saveCaption(editingCaption)">
                    <div class="mb-4">
                        <Field label="Caption">
                            <TextInput
                                v-model="editCaptionForm.caption"
                                placeholder="Deskripsi foto..."
                            />
                        </Field>
                    </div>

                    <div class="flex items-center space-x-3">
                        <button
                            type="submit"
                            :disabled="editCaptionForm.processing"
                            class="flex-1 rounded-lg bg-[#AD7F35] px-4 py-2 font-semibold text-white transition hover:bg-[#5A1B24] disabled:opacity-50"
                        >
                            {{
                                editCaptionForm.processing
                                    ? 'Menyimpan...'
                                    : 'Simpan'
                            }}
                        </button>
                        <button
                            type="button"
                            @click="editingCaption = null"
                            class="flex-1 rounded-lg border-2 border-gray-300 px-4 py-2 font-semibold text-gray-700 transition hover:border-gray-400"
                        >
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>
