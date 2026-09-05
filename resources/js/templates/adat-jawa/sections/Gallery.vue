<script setup lang="ts">
import { ref } from 'vue';

interface GalleryPhoto {
    id: number;
    image_url: string;
    caption?: string;
}

const props = defineProps<{
    gallery: GalleryPhoto[];
}>();

const selectedPhoto = ref<GalleryPhoto | null>(null);

const openLightbox = (photo: GalleryPhoto) => {
    selectedPhoto.value = photo;
};

const closeLightbox = () => {
    selectedPhoto.value = null;
};
</script>

<template>
    <section
        v-if="gallery && gallery.length > 0"
        class="bg-gradient-to-b from-amber-50 to-white px-4 py-20"
    >
        <div class="container mx-auto max-w-6xl">
            <!-- Title -->
            <div class="mb-16 text-center">
                <h2
                    class="mb-4 font-serif text-4xl font-bold text-amber-800 md:text-5xl"
                >
                    Galeri Foto
                </h2>
                <div class="flex items-center justify-center">
                    <div class="h-px w-20 bg-amber-400"></div>
                    <svg
                        class="mx-4 h-8 w-8 text-amber-600"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    <div class="h-px w-20 bg-amber-400"></div>
                </div>
            </div>

            <!-- Gallery Grid -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
                <div
                    v-for="photo in gallery"
                    :key="photo.id"
                    @click="openLightbox(photo)"
                    class="group relative aspect-square cursor-pointer overflow-hidden rounded-lg shadow-lg transition-all duration-300 hover:shadow-2xl"
                >
                    <!-- Image -->
                    <img
                        :src="photo.image_url"
                        :alt="photo.caption || 'Gallery photo'"
                        class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-110"
                    />

                    <!-- Overlay -->
                    <div
                        class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                    >
                        <div class="absolute right-0 bottom-0 left-0 p-4">
                            <p
                                v-if="photo.caption"
                                class="text-sm font-semibold text-white"
                            >
                                {{ photo.caption }}
                            </p>
                        </div>
                        <!-- Zoom Icon -->
                        <div
                            class="absolute inset-0 flex items-center justify-center"
                        >
                            <svg
                                class="h-12 w-12 text-white"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"
                                />
                            </svg>
                        </div>
                    </div>

                    <!-- Batik Corner Decoration -->
                    <div
                        class="absolute top-0 right-0 h-12 w-12 bg-amber-600 opacity-0 transition-opacity group-hover:opacity-100"
                        style="clip-path: polygon(100% 0, 0 0, 100% 100%)"
                    ></div>
                </div>
            </div>
        </div>

        <!-- Lightbox -->
        <Teleport to="body">
            <div
                v-if="selectedPhoto"
                @click="closeLightbox"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
            >
                <!-- Close Button -->
                <button
                    @click="closeLightbox"
                    class="absolute top-4 right-4 z-10 text-white transition hover:text-amber-400"
                >
                    <svg
                        class="h-10 w-10"
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

                <!-- Image -->
                <div @click.stop class="relative max-h-[90vh] max-w-5xl">
                    <img
                        :src="selectedPhoto.image_url"
                        :alt="selectedPhoto.caption || 'Gallery photo'"
                        class="max-h-[90vh] max-w-full rounded-lg object-contain shadow-2xl"
                    />
                    <!-- Caption -->
                    <div
                        v-if="selectedPhoto.caption"
                        class="absolute right-0 bottom-0 left-0 rounded-b-lg bg-gradient-to-t from-black to-transparent p-6"
                    >
                        <p class="text-center text-lg font-semibold text-white">
                            {{ selectedPhoto.caption }}
                        </p>
                    </div>
                </div>
            </div>
        </Teleport>
    </section>
</template>
