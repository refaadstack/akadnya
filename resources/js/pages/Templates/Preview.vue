<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch, onMounted } from 'vue'

interface Template {
  id: number
  slug: string
  name: string
  price: number
}

interface PreviewData {
  bride_name: string
  groom_name: string
  bride_father: string
  bride_mother: string
  groom_father: string
  groom_mother: string
  event_date: string
  akad_datetime: string
  akad_time: string
  akad_venue: string
  akad_maps_url: string
  reception_datetime: string
  reception_time: string
  reception_venue: string
  reception_maps_url: string
  love_story: string
  special_message: string
  cover_photo_url: string
  gallery_urls: Array<{ url: string; caption: string }>
  guest_name: string
  max_pax: number
}

const props = defineProps<{
  template: Template
}>()

// Disable default layout for this page
defineOptions({
  layout: undefined
})

const STORAGE_KEY = `preview_data_${props.template.slug}`
const STORAGE_EXPIRY = 24 * 60 * 60 * 1000 // 24 hours

// Default dummy data structure
const getDefaultData = (): PreviewData => ({
  bride_name: 'Sarah Putri',
  groom_name: 'Ahmad Rizki',
  bride_father: 'Bapak Budi Santoso',
  bride_mother: 'Ibu Siti Rahayu',
  groom_father: 'Bapak Hendra Wijaya',
  groom_mother: 'Ibu Dewi Lestari',
  event_date: '14 Juni 2025',
  akad_datetime: '2025-06-14T09:00:00',
  akad_time: '09:00 WIB',
  akad_venue: 'Masjid Al-Ikhlas, Jakarta Selatan',
  akad_maps_url: 'https://maps.google.com/?q=-6.2608,106.7818',
  reception_datetime: '2025-06-14T11:00:00',
  reception_time: '11:00 WIB',
  reception_venue: 'Gedung Serbaguna Melati, Jakarta Selatan',
  reception_maps_url: 'https://maps.google.com/?q=-6.2608,106.7818',
  love_story: 'Kami bertemu di kampus pada tahun 2020. Dari teman sekelas menjadi sahabat, dan akhirnya menemukan cinta sejati.',
  special_message: 'Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir untuk memberikan doa restu.',
  cover_photo_url: 'https://via.placeholder.com/800x600/FFB6C1/FFFFFF?text=Cover+Photo',
  gallery_urls: [
    { url: 'https://via.placeholder.com/400x300/FFB6C1/FFFFFF?text=Foto+1', caption: 'Foto 1' },
    { url: 'https://via.placeholder.com/400x300/FFB6C1/FFFFFF?text=Foto+2', caption: 'Foto 2' },
    { url: 'https://via.placeholder.com/400x300/FFB6C1/FFFFFF?text=Foto+3', caption: 'Foto 3' }
  ],
  guest_name: 'Tamu Undangan',
  max_pax: 2
})

// Load data from sessionStorage or use default
const loadStoredData = (): PreviewData => {
  try {
    const stored = sessionStorage.getItem(STORAGE_KEY)
    if (stored) {
      const { data, timestamp } = JSON.parse(stored)
      // Check if data is still valid (within 24 hours)
      if (Date.now() - timestamp < STORAGE_EXPIRY) {
        return data
      }
    }
  } catch (e) {
    console.error('Failed to load stored data:', e)
  }
  return getDefaultData()
}

const previewData = ref<PreviewData>(loadStoredData())
const showEditForm = ref(false)
const coverPhotoFile = ref<File | null>(null)
const galleryFiles = ref<File[]>([])

// Preview state
const iframeHtml = ref<string>('')
const iframeSrc = ref<string>('')
const isLoading = ref<boolean>(false)
const previewError = ref<string | null>(null)

// Update iframe src whenever preview data changes
const updateIframeSrc = () => {
  try {
    // Encode preview data as base64 JSON for query string
    const dataJson = JSON.stringify(previewData.value)
    const dataBase64 = btoa(dataJson)
    iframeSrc.value = `/templates/${props.template.slug}/render?data=${encodeURIComponent(dataBase64)}`
  } catch (e) {
    console.error('Failed to encode preview data:', e)
    iframeSrc.value = `/templates/${props.template.slug}/render`
  }
}

// Fetch preview from API (DISABLED - using direct iframe src instead)
const fetchPreview = async () => {
  // Not needed - iframe loads directly from /templates/{slug}/render
  isLoading.value = false
}

// Save to sessionStorage whenever data changes
watch(previewData, (newData) => {
  try {
    sessionStorage.setItem(STORAGE_KEY, JSON.stringify({
      data: newData,
      timestamp: Date.now()
    }))
    // Update iframe src with new data
    updateIframeSrc()
  } catch (e) {
    console.error('Failed to save data:', e)
  }
}, { deep: true })

// Debounced watcher for preview updates (DISABLED - using static iframe)
// let debounceTimer: ReturnType<typeof setTimeout> | null = null
// watch(previewData, () => {
//   if (debounceTimer) {
//     clearTimeout(debounceTimer)
//   }
//   debounceTimer = setTimeout(() => {
//     fetchPreview()
//   }, 500)
// }, { deep: true })

// Handle cover photo upload
const handleCoverPhotoUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) {
    coverPhotoFile.value = file
    previewData.value.cover_photo_url = URL.createObjectURL(file)
  }
}

// Handle gallery photos upload
const handleGalleryUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  const files = Array.from(target.files || [])
  
  files.forEach((file, index) => {
    const url = URL.createObjectURL(file)
    if (previewData.value.gallery_urls[index]) {
      previewData.value.gallery_urls[index].url = url
    } else {
      previewData.value.gallery_urls.push({ url, caption: `Foto ${index + 1}` })
    }
  })
  
  galleryFiles.value = [...galleryFiles.value, ...files]
}

// Handle checkout
const handleCheckout = () => {
  // Data already saved in sessionStorage via watch
  router.visit(`/checkout?template=${props.template.slug}`)
}

// Reset to dummy data
const resetData = () => {
  if (confirm('Reset semua data ke contoh default?')) {
    previewData.value = getDefaultData()
    coverPhotoFile.value = null
    galleryFiles.value = []
  }
}

// Handle back navigation
const handleBack = () => {
  // Clear iframe src before navigation to prevent 403 error
  iframeSrc.value = 'about:blank'
  // Use router.get with explicit URL (no trailing slash)
  setTimeout(() => {
    router.get('/templates')
  }, 50)
}

onMounted(() => {
  // Initialize iframe src with current preview data
  updateIframeSrc()
  
  // Cleanup object URLs on unmount
  return () => {
    if (coverPhotoFile.value) {
      URL.revokeObjectURL(previewData.value.cover_photo_url)
    }
    galleryFiles.value.forEach((_, index) => {
      if (previewData.value.gallery_urls[index]) {
        URL.revokeObjectURL(previewData.value.gallery_urls[index].url)
      }
    })
  }
})
</script>

<template>
  <div class="min-h-screen bg-white">
    <Head :title="`Preview: ${template.name}`" />

    <!-- Floating Action Bar -->
    <div class="fixed top-0 left-0 right-0 bg-white shadow-md z-50">
      <div class="container mx-auto px-4 py-4 flex items-center justify-between">
        <div>
          <h2 class="text-xl font-bold text-gray-900">{{ template.name }}</h2>
          <p class="text-sm text-gray-600">Preview Mode - Edit data untuk melihat hasil</p>
        </div>
        <div class="flex gap-3">
          <button
            @click="showEditForm = !showEditForm"
            class="px-6 py-2 border-2 border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 transition font-semibold"
          >
            {{ showEditForm ? 'Tutup Form' : 'Edit Data' }}
          </button>
          <button
            @click="resetData"
            class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition"
          >
            Reset
          </button>
          <button
            @click="handleBack"
            class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition"
          >
            Kembali
          </button>
          <button
            @click="handleCheckout"
            class="px-6 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 transition font-semibold"
          >
            Beli Sekarang - Rp {{ template.price.toLocaleString('id-ID') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Edit Form Sidebar -->
    <transition
      enter-active-class="transition-transform duration-300"
      enter-from-class="translate-x-full"
      enter-to-class="translate-x-0"
      leave-active-class="transition-transform duration-300"
      leave-from-class="translate-x-0"
      leave-to-class="translate-x-full"
    >
      <div
        v-if="showEditForm"
        class="fixed right-0 top-20 bottom-0 w-96 bg-white shadow-2xl z-40 overflow-y-auto"
      >
        <div class="p-6 space-y-6">
          <h3 class="text-2xl font-bold text-gray-900 mb-4">Edit Data Undangan</h3>

          <!-- Bride & Groom -->
          <div class="space-y-4">
            <h4 class="font-semibold text-gray-700 border-b pb-2">Mempelai</h4>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nama Mempelai Wanita</label>
              <input
                v-model="previewData.bride_name"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nama Mempelai Pria</label>
              <input
                v-model="previewData.groom_name"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Ayah Mempelai Wanita</label>
              <input
                v-model="previewData.bride_father"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Ibu Mempelai Wanita</label>
              <input
                v-model="previewData.bride_mother"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Ayah Mempelai Pria</label>
              <input
                v-model="previewData.groom_father"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Ibu Mempelai Pria</label>
              <input
                v-model="previewData.groom_mother"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
              />
            </div>
          </div>

          <!-- Event Details -->
          <div class="space-y-4">
            <h4 class="font-semibold text-gray-700 border-b pb-2">Detail Acara</h4>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Acara</label>
              <input
                v-model="previewData.event_date"
                type="text"
                placeholder="14 Juni 2025"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Akad (Datetime)</label>
              <input
                v-model="previewData.akad_datetime"
                type="datetime-local"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Akad (Display)</label>
              <input
                v-model="previewData.akad_time"
                type="text"
                placeholder="09:00 WIB"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Akad</label>
              <input
                v-model="previewData.akad_venue"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Google Maps URL Akad</label>
              <input
                v-model="previewData.akad_maps_url"
                type="url"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Resepsi (Display)</label>
              <input
                v-model="previewData.reception_time"
                type="text"
                placeholder="11:00 WIB"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Resepsi</label>
              <input
                v-model="previewData.reception_venue"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Google Maps URL Resepsi</label>
              <input
                v-model="previewData.reception_maps_url"
                type="url"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
              />
            </div>
          </div>

          <!-- Story & Message -->
          <div class="space-y-4">
            <h4 class="font-semibold text-gray-700 border-b pb-2">Cerita & Pesan</h4>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Cerita Cinta</label>
              <textarea
                v-model="previewData.love_story"
                rows="4"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
              ></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Pesan Khusus</label>
              <textarea
                v-model="previewData.special_message"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
              ></textarea>
            </div>
          </div>

          <!-- Photos -->
          <div class="space-y-4">
            <h4 class="font-semibold text-gray-700 border-b pb-2">Foto</h4>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Foto Cover</label>
              <input
                type="file"
                accept="image/*"
                @change="handleCoverPhotoUpload"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
              />
              <p class="text-xs text-gray-500 mt-1">Upload foto untuk preview saja</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Foto Galeri (max 3)</label>
              <input
                type="file"
                accept="image/*"
                multiple
                @change="handleGalleryUpload"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
              />
              <p class="text-xs text-gray-500 mt-1">Upload foto untuk preview saja</p>
            </div>
          </div>

          <div class="pt-4 border-t">
            <p class="text-xs text-gray-500">
              💡 Data disimpan otomatis di browser Anda dan akan dibawa ke halaman checkout.
              Data akan hilang setelah 24 jam atau jika browser cache dibersihkan.
            </p>
          </div>
        </div>
      </div>
    </transition>

    <!-- Template Preview -->
    <div class="pt-24" :class="{ 'mr-96': showEditForm }">
      <!-- Loading State -->
      <div v-if="isLoading" class="flex items-center justify-center min-h-screen">
        <div class="text-center">
          <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-pink-600 mb-4"></div>
          <p class="text-gray-600">Memuat preview...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="previewError" class="flex items-center justify-center min-h-screen">
        <div class="text-center max-w-md mx-auto p-6">
          <div class="text-red-600 text-5xl mb-4">⚠️</div>
          <h3 class="text-xl font-semibold text-gray-900 mb-2">Gagal Memuat Preview</h3>
          <p class="text-gray-600 mb-4">{{ previewError }}</p>
          <button
            @click="fetchPreview"
            class="px-6 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 transition"
          >
            Coba Lagi
          </button>
        </div>
      </div>

      <!-- Preview Iframe -->
      <iframe
        v-else
        :src="iframeSrc"
        class="w-full border-0"
        style="height: calc(100vh - 6rem); min-height: 800px;"
        title="Template Preview"
      />
    </div>
  </div>
</template>
