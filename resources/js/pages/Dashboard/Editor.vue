<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

defineOptions({
  layout: undefined
})

interface GalleryPhoto {
  url: string
  caption?: string
}

interface InvitationContent {
  bride_name: string | null
  bride_father: string | null
  bride_mother: string | null
  bride_photo_url: string | null
  groom_name: string | null
  groom_father: string | null
  groom_mother: string | null
  groom_photo_url: string | null
  akad_datetime: string | null
  akad_venue: string | null
  akad_maps_url: string | null
  reception_datetime: string | null
  reception_venue: string | null
  reception_maps_url: string | null
  love_story: string | null
  special_message: string | null
  cover_photo_url: string | null
  music_url: string | null
  gallery_photos: GalleryPhoto[] | null
  bank_name: string | null
  account_number: string | null
  account_name: string | null
  qris_image_url: string | null
  gopay_number: string | null
  ovo_number: string | null
  dana_number: string | null
}

const props = defineProps<{
  invitation: {
    id: number
    status: string
    template: {
      name: string
      slug: string
    }
  }
  content: InvitationContent | null
}>()

const showUserMenu = ref(false)

const logout = () => {
  router.post('/logout')
}

const form = useForm({
  bride_name: props.content?.bride_name || '',
  bride_father: props.content?.bride_father || '',
  bride_mother: props.content?.bride_mother || '',
  bride_photo_url: props.content?.bride_photo_url || '',
  groom_name: props.content?.groom_name || '',
  groom_father: props.content?.groom_father || '',
  groom_mother: props.content?.groom_mother || '',
  groom_photo_url: props.content?.groom_photo_url || '',
  akad_datetime: props.content?.akad_datetime || '',
  akad_venue: props.content?.akad_venue || '',
  akad_maps_url: props.content?.akad_maps_url || '',
  reception_datetime: props.content?.reception_datetime || '',
  reception_venue: props.content?.reception_venue || '',
  reception_maps_url: props.content?.reception_maps_url || '',
  love_story: props.content?.love_story || '',
  special_message: props.content?.special_message || '',
  cover_photo_url: props.content?.cover_photo_url || '',
  music_url: props.content?.music_url || '',
  gallery_photos: props.content?.gallery_photos || [],
  bank_name: props.content?.bank_name || '',
  account_number: props.content?.account_number || '',
  account_name: props.content?.account_name || '',
  qris_image_url: props.content?.qris_image_url || '',
  gopay_number: props.content?.gopay_number || '',
  ovo_number: props.content?.ovo_number || '',
  dana_number: props.content?.dana_number || '',
})

const uploadingCover = ref(false)
const uploadingMusic = ref(false)
const uploadingQris = ref(false)
const uploadingBride = ref(false)
const uploadingGroom = ref(false)
const uploadingGallery = ref(false)

// Get CSRF token from cookie (works without meta tag)
const getCsrfToken = (): string => {
  return decodeURIComponent(
    document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] ?? ''
  )
}

const uploadFile = async (url: string, file: File): Promise<{ success: boolean; url?: string; message?: string }> => {
  const formData = new FormData()
  formData.append('file', file)

  const response = await fetch(url, {
    method: 'POST',
    headers: {
      'X-XSRF-TOKEN': getCsrfToken(),
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: formData,
  })

  return response.json()
}

const uploadCover = async (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (!file) return

  uploadingCover.value = true
  try {
    const data = await uploadFile('/media/upload/cover', file)
    if (data.success && data.url) {
      form.cover_photo_url = data.url
    } else {
      alert(data.message || 'Gagal upload foto cover')
    }
  } catch (error) {
    console.error('Upload error:', error)
    alert('Terjadi kesalahan saat upload foto cover')
  } finally {
    uploadingCover.value = false
  }
}

const uploadMusic = async (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (!file) return

  uploadingMusic.value = true
  try {
    const data = await uploadFile('/media/upload/music', file)
    if (data.success && data.url) {
      form.music_url = data.url
    } else {
      alert(data.message || 'Gagal upload musik')
    }
  } catch (error) {
    console.error('Upload error:', error)
    alert('Terjadi kesalahan saat upload musik')
  } finally {
    uploadingMusic.value = false
  }
}

const uploadQris = async (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (!file) return

  uploadingQris.value = true
  try {
    const data = await uploadFile('/media/upload/qris', file)
    if (data.success && data.url) {
      form.qris_image_url = data.url
    } else {
      alert(data.message || 'Gagal upload QRIS')
    }
  } catch (error) {
    console.error('Upload error:', error)
    alert('Terjadi kesalahan saat upload QRIS')
  } finally {
    uploadingQris.value = false
  }
}

const uploadBridePhoto = async (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (!file) return

  uploadingBride.value = true
  try {
    const data = await uploadFile('/media/upload/bride', file)
    if (data.success && data.url) {
      form.bride_photo_url = data.url
    } else {
      alert(data.message || 'Gagal upload foto mempelai wanita')
    }
  } catch (error) {
    console.error('Upload error:', error)
    alert('Terjadi kesalahan saat upload foto mempelai wanita')
  } finally {
    uploadingBride.value = false
  }
}

const uploadGroomPhoto = async (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (!file) return

  uploadingGroom.value = true
  try {
    const data = await uploadFile('/media/upload/groom', file)
    if (data.success && data.url) {
      form.groom_photo_url = data.url
    } else {
      alert(data.message || 'Gagal upload foto mempelai pria')
    }
  } catch (error) {
    console.error('Upload error:', error)
    alert('Terjadi kesalahan saat upload foto mempelai pria')
  } finally {
    uploadingGroom.value = false
  }
}

const uploadGalleryPhoto = async (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (!file) return

  uploadingGallery.value = true
  try {
    const data = await uploadFile('/media/upload/gallery', file)
    if (data.success && data.url) {
      if (!Array.isArray(form.gallery_photos)) {
        form.gallery_photos = []
      }
      form.gallery_photos.push({ url: data.url, caption: '' })
    } else {
      alert(data.message || 'Gagal upload foto galeri')
    }
  } catch (error) {
    console.error('Upload error:', error)
    alert('Terjadi kesalahan saat upload foto galeri')
  } finally {
    uploadingGallery.value = false
  }
}

const removeGalleryPhoto = (index: number) => {
  if (Array.isArray(form.gallery_photos)) {
    form.gallery_photos.splice(index, 1)
  }
}

const submit = () => {
  console.log('Submitting form with data:', form.data())
  console.log('Form errors before submit:', form.errors)
  
  form.post('/dashboard/editor', {
    preserveScroll: true,
    onBefore: () => {
      console.log('Form submission started')
    },
    onError: (errors) => {
      console.error('Validation errors:', errors)
      
      // Scroll to top to show error summary
      window.scrollTo({ top: 0, behavior: 'smooth' })
    },
    onSuccess: (page) => {
      console.log('Form submitted successfully', page)
      window.scrollTo({ top: 0, behavior: 'smooth' })
    },
    onFinish: () => {
      console.log('Form submission finished')
    },
  })
}
</script>

<template>
  <div>
    <Head title="Edit Konten Undangan" />

    <div class="min-h-screen bg-gray-50">
      <!-- Navigation -->
      <nav class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-4">
          <div class="flex items-center justify-between h-16">
            <Link href="/dashboard" class="flex items-center space-x-2">
              <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-purple-600 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-xl">M</span>
              </div>
              <span class="text-2xl font-bold bg-gradient-to-r from-pink-600 to-purple-600 bg-clip-text text-transparent">
                MyAkad
              </span>
            </Link>

            <div class="hidden md:flex items-center space-x-8">
              <Link href="/dashboard" class="text-gray-700 hover:text-pink-600 font-medium transition">
                Dashboard
              </Link>
              <Link href="/dashboard/editor" class="text-pink-600 font-medium transition">
                Editor
              </Link>
              <Link href="/dashboard/customize" class="text-gray-700 hover:text-pink-600 font-medium transition">
                Kustomisasi
              </Link>
              <Link href="/dashboard/gallery" class="text-gray-700 hover:text-pink-600 font-medium transition">
                Galeri
              </Link>
              <Link href="/dashboard/guests" class="text-gray-700 hover:text-pink-600 font-medium transition">
                Tamu
              </Link>
              <Link href="/dashboard/rsvp" class="text-gray-700 hover:text-pink-600 font-medium transition">
                RSVP
              </Link>
            </div>

            <div class="relative">
              <button @click="showUserMenu = !showUserMenu" class="flex items-center space-x-3 focus:outline-none">
                <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-purple-600 rounded-full flex items-center justify-center">
                  <span class="text-white font-semibold text-sm">
                    {{ $page.props.auth?.user?.name?.charAt(0).toUpperCase() || 'U' }}
                  </span>
                </div>
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              <div v-show="showUserMenu" @click="showUserMenu = false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50">
                <div class="px-4 py-2 border-b border-gray-100">
                  <p class="text-sm font-semibold text-gray-900">{{ $page.props.auth?.user?.name }}</p>
                  <p class="text-xs text-gray-600">{{ $page.props.auth?.user?.email }}</p>
                </div>
                
                <Link href="/settings/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition">
                  <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Profil
                  </div>
                </Link>

                <Link href="/settings/security" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition">
                  <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Keamanan
                  </div>
                </Link>

                <div class="border-t border-gray-100 mt-2 pt-2">
                  <button @click="logout" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                    <div class="flex items-center">
                      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                      </svg>
                      Keluar
                    </div>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </nav>

      <!-- Main Content -->
      <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <Link href="/dashboard" class="text-sm text-gray-600 hover:text-pink-600 mb-2 inline-flex items-center">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              Kembali ke Dashboard
            </Link>
            <h1 class="text-3xl font-bold text-gray-900">Edit Konten Undangan</h1>
            <p class="text-gray-600 mt-1">Template: {{ invitation.template.name }}</p>
          </div>
        </div>

        <!-- Flash Messages -->
        <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 flex items-center">
          <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          {{ $page.props.flash.success }}
        </div>

        <div v-if="$page.props.flash?.error" class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6 flex items-center">
          <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
          {{ $page.props.flash.error }}
        </div>

        <!-- Validation Errors Summary -->
        <div v-if="Object.keys(form.errors).length > 0" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
          <div class="flex items-start">
            <svg class="w-5 h-5 text-red-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <div class="flex-1">
              <h3 class="text-sm font-semibold text-red-800 mb-2">Terdapat kesalahan pada form:</h3>
              <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                <li v-for="(error, field) in form.errors" :key="field">
                  {{ error }}
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="max-w-4xl">
          <!-- Mempelai Wanita -->
          <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Mempelai Wanita</h2>
            
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
                <input
                  v-model="form.bride_name"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                  placeholder="Contoh: Siti Nurhaliza"
                  required
                />
                <p v-if="form.errors.bride_name" class="text-red-600 text-sm mt-1">{{ form.errors.bride_name }}</p>
              </div>

              <div class="grid md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Nama Ayah</label>
                  <input
                    v-model="form.bride_father"
                    type="text"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                    placeholder="Contoh: Bapak Ahmad"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Nama Ibu</label>
                  <input
                    v-model="form.bride_mother"
                    type="text"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                    placeholder="Contoh: Ibu Siti"
                  />
                </div>
              </div>

              <!-- Bride Photo -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Foto Mempelai Wanita</label>
                <div class="flex items-start space-x-4">
                  <div v-if="form.bride_photo_url" class="relative w-32 h-40 rounded-lg overflow-hidden border-2 border-gray-200">
                    <img :src="form.bride_photo_url" alt="Bride" class="w-full h-full object-cover" />
                    <button
                      type="button"
                      @click="form.bride_photo_url = ''"
                      class="absolute top-2 right-2 bg-red-500 text-white p-1 rounded-full hover:bg-red-600"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                  
                  <div class="flex-1">
                    <input
                      type="file"
                      @change="uploadBridePhoto"
                      accept="image/*"
                      class="hidden"
                      id="bride-upload"
                      :disabled="uploadingBride"
                    />
                    <label
                      for="bride-upload"
                      class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition"
                      :class="{ 'opacity-50 cursor-not-allowed': uploadingBride }"
                    >
                      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      {{ uploadingBride ? 'Uploading...' : 'Pilih Foto' }}
                    </label>
                    <p class="text-xs text-gray-500 mt-2">Format: JPG, PNG, WebP. Maksimal 5MB. Rasio potret (3:4) direkomendasikan</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Mempelai Pria -->
          <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Mempelai Pria</h2>
            
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
                <input
                  v-model="form.groom_name"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                  placeholder="Contoh: Muhammad Rizki"
                  required
                />
                <p v-if="form.errors.groom_name" class="text-red-600 text-sm mt-1">{{ form.errors.groom_name }}</p>
              </div>

              <div class="grid md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Nama Ayah</label>
                  <input
                    v-model="form.groom_father"
                    type="text"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                    placeholder="Contoh: Bapak Budi"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Nama Ibu</label>
                  <input
                    v-model="form.groom_mother"
                    type="text"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                    placeholder="Contoh: Ibu Ani"
                  />
                </div>
              </div>

              <!-- Groom Photo -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Foto Mempelai Pria</label>
                <div class="flex items-start space-x-4">
                  <div v-if="form.groom_photo_url" class="relative w-32 h-40 rounded-lg overflow-hidden border-2 border-gray-200">
                    <img :src="form.groom_photo_url" alt="Groom" class="w-full h-full object-cover" />
                    <button
                      type="button"
                      @click="form.groom_photo_url = ''"
                      class="absolute top-2 right-2 bg-red-500 text-white p-1 rounded-full hover:bg-red-600"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                  
                  <div class="flex-1">
                    <input
                      type="file"
                      @change="uploadGroomPhoto"
                      accept="image/*"
                      class="hidden"
                      id="groom-upload"
                      :disabled="uploadingGroom"
                    />
                    <label
                      for="groom-upload"
                      class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition"
                      :class="{ 'opacity-50 cursor-not-allowed': uploadingGroom }"
                    >
                      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      {{ uploadingGroom ? 'Uploading...' : 'Pilih Foto' }}
                    </label>
                    <p class="text-xs text-gray-500 mt-2">Format: JPG, PNG, WebP. Maksimal 5MB. Rasio potret (3:4) direkomendasikan</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Akad Nikah -->
          <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Akad Nikah</h2>
            
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal & Waktu *</label>
                <input
                  v-model="form.akad_datetime"
                  type="datetime-local"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                  required
                />
                <p v-if="form.errors.akad_datetime" class="text-red-600 text-sm mt-1">{{ form.errors.akad_datetime }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Tempat *</label>
                <input
                  v-model="form.akad_venue"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                  placeholder="Contoh: Masjid Al-Ikhlas"
                  required
                />
                <p v-if="form.errors.akad_venue" class="text-red-600 text-sm mt-1">{{ form.errors.akad_venue }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Link Google Maps</label>
                <input
                  v-model="form.akad_maps_url"
                  type="url"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                  placeholder="https://maps.google.com/..."
                />
                <p v-if="form.errors.akad_maps_url" class="text-red-600 text-sm mt-1">{{ form.errors.akad_maps_url }}</p>
              </div>
            </div>
          </div>

          <!-- Resepsi -->
          <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Resepsi (Opsional)</h2>
            
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal & Waktu</label>
                <input
                  v-model="form.reception_datetime"
                  type="datetime-local"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Tempat</label>
                <input
                  v-model="form.reception_venue"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                  placeholder="Contoh: Gedung Serbaguna"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Link Google Maps</label>
                <input
                  v-model="form.reception_maps_url"
                  type="url"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                  placeholder="https://maps.google.com/..."
                />
              </div>
            </div>
          </div>

          <!-- Media -->
          <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Media</h2>
            
            <div class="space-y-6">
              <!-- Cover Photo -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Foto Cover</label>
                <div class="flex items-start space-x-4">
                  <div v-if="form.cover_photo_url" class="relative w-48 h-32 rounded-lg overflow-hidden border-2 border-gray-200">
                    <img :src="form.cover_photo_url" alt="Cover" class="w-full h-full object-cover" />
                    <button
                      type="button"
                      @click="form.cover_photo_url = ''"
                      class="absolute top-2 right-2 bg-red-500 text-white p-1 rounded-full hover:bg-red-600"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                  
                  <div class="flex-1">
                    <input
                      type="file"
                      @change="uploadCover"
                      accept="image/*"
                      class="hidden"
                      id="cover-upload"
                      :disabled="uploadingCover"
                    />
                    <label
                      for="cover-upload"
                      class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition"
                      :class="{ 'opacity-50 cursor-not-allowed': uploadingCover }"
                    >
                      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      {{ uploadingCover ? 'Uploading...' : 'Pilih Foto' }}
                    </label>
                    <p class="text-xs text-gray-500 mt-2">Format: JPG, PNG, WebP. Maksimal 5MB</p>
                  </div>
                </div>
              </div>

              <!-- Music -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Musik Latar</label>
                <div class="space-y-3">
                  <div v-if="form.music_url" class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                    <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                    </svg>
                    <div class="flex-1">
                      <p class="text-sm font-medium text-gray-900">Musik telah diupload</p>
                      <audio controls class="w-full mt-2">
                        <source :src="form.music_url" type="audio/mpeg">
                      </audio>
                    </div>
                    <button
                      type="button"
                      @click="form.music_url = ''"
                      class="text-red-600 hover:text-red-700"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                  
                  <div>
                    <input
                      type="file"
                      @change="uploadMusic"
                      accept="audio/*"
                      class="hidden"
                      id="music-upload"
                      :disabled="uploadingMusic"
                    />
                    <label
                      for="music-upload"
                      class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition"
                      :class="{ 'opacity-50 cursor-not-allowed': uploadingMusic }"
                    >
                      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                      </svg>
                      {{ uploadingMusic ? 'Uploading...' : 'Upload Musik' }}
                    </label>
                    <p class="text-xs text-gray-500 mt-2">Format: MP3, WAV. Maksimal 10MB</p>
                  </div>
                </div>
              </div>

              <!-- Gallery Photos -->
              <div class="border-t pt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Galeri Foto</label>
                
                <!-- Gallery Grid -->
                <div v-if="Array.isArray(form.gallery_photos) && form.gallery_photos.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                  <div v-for="(photo, index) in form.gallery_photos" :key="index" class="relative group">
                    <div class="aspect-square rounded-lg overflow-hidden border-2 border-gray-200">
                      <img :src="photo.url" :alt="photo.caption || `Gallery ${index + 1}`" class="w-full h-full object-cover" />
                    </div>
                    <button
                      type="button"
                      @click="removeGalleryPhoto(index)"
                      class="absolute top-2 right-2 bg-red-500 text-white p-1.5 rounded-full hover:bg-red-600 opacity-0 group-hover:opacity-100 transition-opacity"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                    <!-- Caption Input -->
                    <input
                      v-model="photo.caption"
                      type="text"
                      class="mt-2 w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-pink-500 focus:border-transparent"
                      placeholder="Caption (opsional)"
                    />
                  </div>
                </div>

                <!-- Upload Button -->
                <div>
                  <input
                    type="file"
                    @change="uploadGalleryPhoto"
                    accept="image/*"
                    class="hidden"
                    id="gallery-upload"
                    :disabled="uploadingGallery"
                  />
                  <label
                    for="gallery-upload"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition"
                    :class="{ 'opacity-50 cursor-not-allowed': uploadingGallery }"
                  >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ uploadingGallery ? 'Uploading...' : 'Tambah Foto Galeri' }}
                  </label>
                  <p class="text-xs text-gray-500 mt-2">Format: JPG, PNG, WebP. Maksimal 5MB per foto. Rasio persegi (1:1) direkomendasikan</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Cerita & Pesan -->
          <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Cerita & Pesan</h2>
            
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cerita Cinta</label>
                <textarea
                  v-model="form.love_story"
                  rows="5"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                  placeholder="Ceritakan kisah cinta Anda..."
                ></textarea>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Pesan Khusus</label>
                <textarea
                  v-model="form.special_message"
                  rows="3"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                  placeholder="Pesan untuk tamu undangan..."
                ></textarea>
              </div>
            </div>
          </div>

          <!-- Amplop Digital -->
          <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
              <div>
                <h2 class="text-xl font-bold text-gray-900">Amplop Digital</h2>
                <p class="text-sm text-gray-600 mt-1">Terima hadiah dari tamu secara digital</p>
              </div>
              <div class="bg-gradient-to-r from-pink-100 to-purple-100 px-4 py-2 rounded-lg">
                <span class="text-sm font-semibold text-pink-700">Opsional</span>
              </div>
            </div>
            
            <div class="space-y-6">
              <!-- Transfer Bank -->
              <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                  </svg>
                  Transfer Bank
                </h3>
                
                <div class="grid md:grid-cols-3 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Bank</label>
                    <input
                      v-model="form.bank_name"
                      type="text"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                      placeholder="Contoh: BCA"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Rekening</label>
                    <input
                      v-model="form.account_number"
                      type="text"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                      placeholder="1234567890"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Pemilik Rekening</label>
                    <input
                      v-model="form.account_name"
                      type="text"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                      placeholder="Nama sesuai rekening"
                    />
                  </div>
                </div>
              </div>

              <!-- QRIS -->
              <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                  </svg>
                  QRIS
                </h3>
                
                <div class="flex items-start space-x-4">
                  <div v-if="form.qris_image_url" class="relative w-48 h-48 rounded-lg overflow-hidden border-2 border-gray-200">
                    <img :src="form.qris_image_url" alt="QRIS" class="w-full h-full object-cover" />
                    <button
                      type="button"
                      @click="form.qris_image_url = ''"
                      class="absolute top-2 right-2 bg-red-500 text-white p-1 rounded-full hover:bg-red-600"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                  
                  <div class="flex-1">
                    <input
                      type="file"
                      @change="uploadQris"
                      accept="image/*"
                      class="hidden"
                      id="qris-upload"
                      :disabled="uploadingQris"
                    />
                    <label
                      for="qris-upload"
                      class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition"
                      :class="{ 'opacity-50 cursor-not-allowed': uploadingQris }"
                    >
                      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      {{ uploadingQris ? 'Uploading...' : 'Upload QRIS' }}
                    </label>
                    <p class="text-xs text-gray-500 mt-2">Upload gambar QR Code untuk pembayaran QRIS</p>
                  </div>
                </div>
              </div>

              <!-- E-Wallet -->
              <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                  </svg>
                  E-Wallet
                </h3>
                
                <div class="grid md:grid-cols-3 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                      <span class="bg-blue-500 text-white text-xs font-bold px-2 py-0.5 rounded mr-2">GoPay</span>
                      Nomor HP
                    </label>
                    <input
                      v-model="form.gopay_number"
                      type="text"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                      placeholder="08123456789"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                      <span class="bg-purple-600 text-white text-xs font-bold px-2 py-0.5 rounded mr-2">OVO</span>
                      Nomor HP
                    </label>
                    <input
                      v-model="form.ovo_number"
                      type="text"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                      placeholder="08123456789"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                      <span class="bg-blue-400 text-white text-xs font-bold px-2 py-0.5 rounded mr-2">DANA</span>
                      Nomor HP
                    </label>
                    <input
                      v-model="form.dana_number"
                      type="text"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                      placeholder="08123456789"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <button
                type="submit"
                :disabled="form.processing"
                class="bg-gradient-to-r from-pink-600 to-purple-600 text-white px-8 py-3 rounded-lg font-semibold hover:shadow-lg transition disabled:opacity-50"
              >
                {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
              </button>

              <Link
                href="/dashboard"
                class="text-gray-600 hover:text-gray-800 font-medium"
              >
                Batal
              </Link>
            </div>

            <Link
              :href="`/dashboard/editor/preview`"
              target="_blank"
              class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition flex items-center"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              Preview
            </Link>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
