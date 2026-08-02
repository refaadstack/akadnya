<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'

interface Photo {
  id: number
  image_url: string
  caption: string | null
  sort_order: number
}

const props = defineProps<{
  gallery: Photo[]
}>()

const uploadForm = useForm({
  file: null as File | null,
  caption: '',
})

const editingCaption = ref<number | null>(null)
const editCaptionForm = useForm({
  caption: '',
})

const draggedItem = ref<number | null>(null)

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) {
    uploadForm.file = file
  }
}

const uploadPhoto = () => {
  if (!uploadForm.file) return

  const formData = new FormData()
  formData.append('file', uploadForm.file)
  formData.append('caption', uploadForm.caption)

  uploadForm.post('/dashboard/gallery', {
    preserveScroll: true,
    onSuccess: () => {
      uploadForm.reset()
      const fileInput = document.getElementById('photo-upload') as HTMLInputElement
      if (fileInput) fileInput.value = ''
    },
  })
}

const startEditCaption = (photo: Photo) => {
  editingCaption.value = photo.id
  editCaptionForm.caption = photo.caption || ''
}

const saveCaption = (photoId: number) => {
  editCaptionForm.post(`/dashboard/gallery/${photoId}`, {
    preserveScroll: true,
    onSuccess: () => {
      editingCaption.value = null
    },
  })
}

const deletePhoto = (photoId: number) => {
  if (confirm('Yakin ingin menghapus foto ini?')) {
    router.delete(`/dashboard/gallery/${photoId}`)
  }
}

const onDragStart = (index: number) => {
  draggedItem.value = index
}

const onDragOver = (event: DragEvent) => {
  event.preventDefault()
}

const onDrop = (event: DragEvent, dropIndex: number) => {
  event.preventDefault()
  
  if (draggedItem.value === null) return

  const items = [...props.gallery]
  const draggedPhoto = items[draggedItem.value]
  
  items.splice(draggedItem.value, 1)
  items.splice(dropIndex, 0, draggedPhoto)

  // Update sort_order
  const reorderedPhotos = items.map((photo, index) => ({
    id: photo.id,
    sort_order: index,
  }))

  router.post('/dashboard/gallery/reorder', {
    photos: reorderedPhotos,
  }, {
    preserveScroll: true,
  })

  draggedItem.value = null
}
</script>

<template>
  <DashboardLayout>
    <Head title="Galeri Foto" />

    <!-- Content -->
    <div class="container mx-auto px-4 py-8">
      <!-- Flash Messages -->
      <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
        {{ $page.props.flash.success }}
      </div>

      <div v-if="$page.props.flash?.error" class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
        {{ $page.props.flash.error }}
      </div>

        <div class="max-w-6xl">
          <!-- Upload Section -->
          <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Upload Foto Baru</h2>
            
            <form @submit.prevent="uploadPhoto">
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Foto</label>
                  <input
                    type="file"
                    @change="handleFileChange"
                    accept="image/*"
                    id="photo-upload"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                    required
                  />
                  <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, WebP. Maksimal 5MB</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Caption (Opsional)</label>
                  <input
                    v-model="uploadForm.caption"
                    type="text"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                    placeholder="Deskripsi foto..."
                  />
                </div>

                <button
                  type="submit"
                  :disabled="uploadForm.processing || !uploadForm.file"
                  class="bg-gradient-to-r from-pink-600 to-purple-600 text-white px-6 py-2 rounded-lg font-semibold hover:shadow-lg transition disabled:opacity-50"
                >
                  {{ uploadForm.processing ? 'Uploading...' : 'Upload Foto' }}
                </button>
              </div>
            </form>
          </div>

          <!-- Gallery Grid -->
          <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-xl font-bold text-gray-900">Galeri ({{ gallery.length }} foto)</h2>
              <p class="text-sm text-gray-600">Drag & drop untuk mengubah urutan</p>
            </div>

            <!-- Empty State -->
            <div v-if="gallery.length === 0" class="text-center py-16">
              <svg class="w-24 h-24 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <h3 class="text-xl font-semibold text-gray-900 mb-2">Belum ada foto</h3>
              <p class="text-gray-600">Upload foto pertama Anda untuk galeri undangan</p>
            </div>

            <!-- Photos Grid -->
            <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
              <div
                v-for="(photo, index) in gallery"
                :key="photo.id"
                draggable="true"
                @dragstart="onDragStart(index)"
                @dragover="onDragOver"
                @drop="onDrop($event, index)"
                class="relative group cursor-move bg-gray-100 rounded-lg overflow-hidden aspect-square"
              >
                <!-- Image -->
                <img
                  :src="photo.image_url"
                  :alt="photo.caption || 'Gallery photo'"
                  class="w-full h-full object-cover"
                />

                <!-- Overlay -->
                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition flex items-center justify-center">
                  <div class="opacity-0 group-hover:opacity-100 transition space-x-2">
                    <button
                      @click="startEditCaption(photo)"
                      class="bg-blue-600 text-white p-2 rounded-lg hover:bg-blue-700 transition"
                      title="Edit caption"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button
                      @click="deletePhoto(photo.id)"
                      class="bg-red-600 text-white p-2 rounded-lg hover:bg-red-700 transition"
                      title="Hapus foto"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </div>

                <!-- Caption -->
                <div v-if="photo.caption" class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-3">
                  <p class="text-white text-sm truncate">{{ photo.caption }}</p>
                </div>

                <!-- Sort Order Badge -->
                <div class="absolute top-2 left-2 bg-gray-900 bg-opacity-75 text-white text-xs font-semibold px-2 py-1 rounded">
                  #{{ index + 1 }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    <!-- Edit Caption Modal -->
    <div
      v-if="editingCaption !== null"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="editingCaption = null"
    >
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Edit Caption</h3>
        
        <form @submit.prevent="saveCaption(editingCaption)">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Caption</label>
            <input
              v-model="editCaptionForm.caption"
              type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
              placeholder="Deskripsi foto..."
            />
          </div>

          <div class="flex items-center space-x-3">
            <button
              type="submit"
              :disabled="editCaptionForm.processing"
              class="flex-1 bg-pink-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-pink-700 transition disabled:opacity-50"
            >
              {{ editCaptionForm.processing ? 'Menyimpan...' : 'Simpan' }}
            </button>
            <button
              type="button"
              @click="editingCaption = null"
              class="flex-1 bg-gray-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-gray-700 transition"
            >
              Batal
            </button>
          </div>
        </form>
      </div>
    </div>
  </DashboardLayout>
</template>
