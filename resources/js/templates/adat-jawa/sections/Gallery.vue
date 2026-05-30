<script setup lang="ts">
import { ref } from 'vue'

interface GalleryPhoto {
  id: number
  image_url: string
  caption?: string
}

const props = defineProps<{
  gallery: GalleryPhoto[]
}>()

const selectedPhoto = ref<GalleryPhoto | null>(null)

const openLightbox = (photo: GalleryPhoto) => {
  selectedPhoto.value = photo
}

const closeLightbox = () => {
  selectedPhoto.value = null
}
</script>

<template>
  <section v-if="gallery && gallery.length > 0" class="py-20 px-4 bg-gradient-to-b from-amber-50 to-white">
    <div class="container mx-auto max-w-6xl">
      <!-- Title -->
      <div class="text-center mb-16">
        <h2 class="text-4xl md:text-5xl font-bold text-amber-800 mb-4 font-serif">Galeri Foto</h2>
        <div class="flex items-center justify-center">
          <div class="h-px w-20 bg-amber-400"></div>
          <svg class="w-8 h-8 mx-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
          </svg>
          <div class="h-px w-20 bg-amber-400"></div>
        </div>
      </div>

      <!-- Gallery Grid -->
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <div
          v-for="photo in gallery"
          :key="photo.id"
          @click="openLightbox(photo)"
          class="relative aspect-square rounded-lg overflow-hidden cursor-pointer group shadow-lg hover:shadow-2xl transition-all duration-300"
        >
          <!-- Image -->
          <img
            :src="photo.image_url"
            :alt="photo.caption || 'Gallery photo'"
            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
          />

          <!-- Overlay -->
          <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <div class="absolute bottom-0 left-0 right-0 p-4">
              <p v-if="photo.caption" class="text-white text-sm font-semibold">{{ photo.caption }}</p>
            </div>
            <!-- Zoom Icon -->
            <div class="absolute inset-0 flex items-center justify-center">
              <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" />
              </svg>
            </div>
          </div>

          <!-- Batik Corner Decoration -->
          <div class="absolute top-0 right-0 w-12 h-12 bg-amber-600 opacity-0 group-hover:opacity-100 transition-opacity" style="clip-path: polygon(100% 0, 0 0, 100% 100%)"></div>
        </div>
      </div>
    </div>

    <!-- Lightbox -->
    <Teleport to="body">
      <div
        v-if="selectedPhoto"
        @click="closeLightbox"
        class="fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center p-4"
      >
        <!-- Close Button -->
        <button
          @click="closeLightbox"
          class="absolute top-4 right-4 text-white hover:text-amber-400 transition z-10"
        >
          <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <!-- Image -->
        <div @click.stop class="max-w-5xl max-h-[90vh] relative">
          <img
            :src="selectedPhoto.image_url"
            :alt="selectedPhoto.caption || 'Gallery photo'"
            class="max-w-full max-h-[90vh] object-contain rounded-lg shadow-2xl"
          />
          <!-- Caption -->
          <div v-if="selectedPhoto.caption" class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-6 rounded-b-lg">
            <p class="text-white text-lg font-semibold text-center">{{ selectedPhoto.caption }}</p>
          </div>
        </div>
      </div>
    </Teleport>
  </section>
</template>
