<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'

defineOptions({
  layout: AdminLayout
})

const form = useForm({
  zip_file: null as File | null,
})

const fileName = ref<string>('')

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) {
    form.zip_file = file
    fileName.value = file.name
  }
}

const submit = () => {
  form.post('/admin/templates/upload-zip', {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      fileName.value = ''
    },
  })
}
</script>

<template>
  <div>
    <Head title="Upload Template - Admin" />

    <div class="min-h-screen bg-gray-50">
      <!-- Header -->
      <div class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-4 py-6">
          <div class="flex items-center space-x-4">
            <Link href="/admin/templates" class="text-gray-600 hover:text-gray-900">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </Link>
            <div>
              <h1 class="text-3xl font-bold text-gray-900">Upload Template</h1>
              <p class="text-gray-600 mt-1">Upload template undangan dalam format ZIP</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
          <!-- Flash Messages -->
          <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
            {{ $page.props.flash.success }}
          </div>
          <div v-if="$page.props.errors?.error" class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
            {{ $page.props.errors.error }}
          </div>

          <!-- Upload Form -->
          <div class="bg-white rounded-xl shadow-md p-8 mb-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Upload File ZIP</h2>

            <form @submit.prevent="submit">
              <!-- File Upload Area -->
              <div class="border-2 border-dashed border-gray-300 rounded-xl p-12 text-center hover:border-pink-500 transition">
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
                    <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    
                    <p v-if="!fileName" class="text-lg font-semibold text-gray-700 mb-2">
                      Klik untuk pilih file ZIP
                    </p>
                    <p v-else class="text-lg font-semibold text-pink-600 mb-2">
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
                  :disabled="form.processing || !form.zip_file"
                  class="bg-gradient-to-r from-pink-600 to-purple-600 text-white px-8 py-3 rounded-lg font-semibold hover:shadow-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {{ form.processing ? 'Mengupload...' : 'Upload & Sync Template' }}
                </button>

                <Link
                  href="/admin/templates"
                  class="text-gray-600 hover:text-gray-800 font-medium"
                >
                  Batal
                </Link>
              </div>
            </form>
          </div>

          <!-- Instructions -->
          <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6">
            <h3 class="text-lg font-bold text-blue-900 mb-4 flex items-center">
              <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Cara Upload Template
            </h3>
            
            <ol class="space-y-3 text-sm text-blue-900">
              <li class="flex items-start">
                <span class="font-bold mr-2">1.</span>
                <span>Siapkan folder template dengan struktur yang benar (lihat panduan di bawah)</span>
              </li>
              <li class="flex items-start">
                <span class="font-bold mr-2">2.</span>
                <span>Compress folder template menjadi file ZIP</span>
              </li>
              <li class="flex items-start">
                <span class="font-bold mr-2">3.</span>
                <span>Upload file ZIP menggunakan form di atas</span>
              </li>
              <li class="flex items-start">
                <span class="font-bold mr-2">4.</span>
                <span>System akan otomatis extract, validasi, dan sync ke database</span>
              </li>
              <li class="flex items-start">
                <span class="font-bold mr-2">5.</span>
                <span>Template siap digunakan!</span>
              </li>
            </ol>
          </div>

          <!-- Template Structure Guide -->
          <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">📁 Struktur Template yang Benar</h3>
            
            <div class="bg-gray-900 text-green-400 p-4 rounded-lg font-mono text-sm overflow-x-auto mb-4">
<pre>adat-jawa/                    ← Folder utama (nama bebas)
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
    └── ornament3.html</pre>
            </div>

            <h4 class="font-bold text-gray-900 mb-2">Contoh template.json:</h4>
            <div class="bg-gray-900 text-yellow-300 p-4 rounded-lg font-mono text-xs overflow-x-auto">
<pre>{
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
}</pre>
            </div>

            <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
              <p class="text-sm text-yellow-900">
                <strong>💡 Tips:</strong> Lihat contoh template lengkap di folder 
                <code class="bg-yellow-200 px-2 py-1 rounded">storage/templates/adat-jawa/</code>
                untuk referensi struktur yang benar.
              </p>
            </div>

            <div class="mt-4">
              <Link
                href="/admin/templates"
                class="inline-flex items-center text-pink-600 hover:text-pink-700 font-semibold"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
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
