<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

interface Invitation {
  id: number
  subdomain: string
  custom_domain: string | null
  status: string
  view_count: number
  public_url: string
  is_published: boolean
}

const props = defineProps<{
  invitation: Invitation
}>()

const subdomainForm = useForm({
  subdomain: props.invitation.subdomain,
})

const customDomainForm = useForm({
  custom_domain: props.invitation.custom_domain || '',
})

const showCopied = ref(false)
const showSubdomainGuide = ref(false)
const showCustomDomainGuide = ref(false)

const copyUrl = () => {
  navigator.clipboard.writeText(props.invitation.public_url)
  showCopied.value = true
  setTimeout(() => {
    showCopied.value = false
  }, 2000)
}

const generateSubdomain = async () => {
  try {
    const xsrfToken = decodeURIComponent(
      document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] ?? ''
    )
    const response = await fetch('/dashboard/settings/generate-subdomain', {
      method: 'POST',
      headers: {
        'X-XSRF-TOKEN': xsrfToken,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    })
    const data = await response.json()
    subdomainForm.subdomain = data.subdomain
  } catch (error) {
    console.error('Failed to generate subdomain:', error)
  }
}

const updateSubdomain = () => {
  subdomainForm.post('/dashboard/settings/subdomain', {
    preserveScroll: true,
  })
}

const updateCustomDomain = () => {
  customDomainForm.post('/dashboard/settings/custom-domain', {
    preserveScroll: true,
  })
}

const publish = () => {
  router.post('/dashboard/settings/publish')
}

const unpublish = () => {
  if (confirm('Yakin ingin unpublish undangan? Tamu tidak akan bisa mengakses undangan.')) {
    router.post('/dashboard/settings/unpublish')
  }
}
</script>

<template>
  <div>
    <Head title="Pengaturan Undangan" />

    <div class="min-h-screen bg-gray-50">
      <!-- Header -->
      <div class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-4 py-6">
          <div class="flex items-center space-x-4">
            <Link href="/dashboard" class="text-gray-600 hover:text-gray-900">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </Link>
            <div>
              <h1 class="text-3xl font-bold text-gray-900">Pengaturan Undangan</h1>
              <p class="text-gray-600 mt-1">Kelola domain dan publikasi undangan</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="container mx-auto px-4 py-8">
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

        <div class="max-w-3xl">
          <!-- Status & Publish -->
          <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Status Publikasi</h2>
            
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg mb-4">
              <div class="flex items-center space-x-3">
                <div
                  class="w-3 h-3 rounded-full"
                  :class="invitation.is_published ? 'bg-green-500' : 'bg-gray-400'"
                ></div>
                <div>
                  <p class="font-semibold text-gray-900">
                    {{ invitation.is_published ? 'Dipublikasikan' : 'Draft' }}
                  </p>
                  <p class="text-sm text-gray-600">
                    {{ invitation.is_published ? 'Undangan dapat diakses publik' : 'Undangan belum dipublikasikan' }}
                  </p>
                </div>
              </div>
              
              <button
                v-if="!invitation.is_published"
                @click="publish"
                class="bg-gradient-to-r from-pink-600 to-purple-600 text-white px-6 py-2 rounded-lg font-semibold hover:shadow-lg transition"
              >
                Publikasikan
              </button>
              <button
                v-else
                @click="unpublish"
                class="bg-gray-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-gray-700 transition"
              >
                Unpublish
              </button>
            </div>

            <div v-if="invitation.is_published" class="border-t pt-4">
              <p class="text-sm text-gray-600 mb-2">URL Undangan:</p>
              <div class="flex items-center space-x-2">
                <input
                  type="text"
                  :value="invitation.public_url"
                  readonly
                  class="flex-1 px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-700"
                />
                <button
                  @click="copyUrl"
                  class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700 transition flex items-center"
                >
                  <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                  </svg>
                  {{ showCopied ? 'Tersalin!' : 'Salin' }}
                </button>
              </div>
              
              <div class="mt-4 flex items-center space-x-4 text-sm text-gray-600">
                <div class="flex items-center">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  {{ invitation.view_count }} views
                </div>
              </div>
            </div>
          </div>

          <!-- Subdomain -->
          <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-xl font-bold text-gray-900">Subdomain</h2>
              <button
                @click="showSubdomainGuide = !showSubdomainGuide"
                class="text-blue-600 hover:text-blue-700 text-sm font-medium flex items-center"
              >
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ showSubdomainGuide ? 'Sembunyikan' : 'Panduan' }}
              </button>
            </div>

            <!-- Guide Section -->
            <div v-if="showSubdomainGuide" class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-5">
              <h3 class="font-bold text-blue-900 mb-3 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                Panduan Subdomain
              </h3>
              
              <div class="space-y-3 text-sm text-blue-900">
                <div>
                  <p class="font-semibold mb-1">📌 Apa itu Subdomain?</p>
                  <p class="text-blue-800">Subdomain adalah alamat unik untuk undangan Anda. Contoh: <code class="bg-blue-100 px-2 py-0.5 rounded">siti-budi-2024.myakad.test</code></p>
                </div>

                <div>
                  <p class="font-semibold mb-1">🔄 Kenapa Auto-Generate?</p>
                  <p class="text-blue-800">Sistem otomatis membuat subdomain dari nama mempelai atau nama Anda untuk kemudahan. Tapi tenang, Anda bisa mengubahnya kapan saja!</p>
                </div>

                <div>
                  <p class="font-semibold mb-1">✅ Aturan Subdomain:</p>
                  <ul class="list-disc list-inside text-blue-800 space-y-1 ml-2">
                    <li>Minimal 3 karakter, maksimal 50 karakter</li>
                    <li>Hanya huruf kecil (a-z), angka (0-9), dan tanda hubung (-)</li>
                    <li>Tidak boleh spasi atau karakter khusus lain</li>
                    <li>Harus unik (belum dipakai user lain)</li>
                  </ul>
                </div>

                <div>
                  <p class="font-semibold mb-1">💡 Tips Memilih Subdomain:</p>
                  <ul class="list-disc list-inside text-blue-800 space-y-1 ml-2">
                    <li><strong>Mudah diingat:</strong> <code class="bg-blue-100 px-1 rounded">siti-dan-budi</code></li>
                    <li><strong>Tambah tahun:</strong> <code class="bg-blue-100 px-1 rounded">pernikahan-kami-2024</code></li>
                    <li><strong>Singkat & jelas:</strong> <code class="bg-blue-100 px-1 rounded">wedding-jakarta</code></li>
                  </ul>
                </div>

                <div class="bg-white rounded-lg p-3 border border-blue-200">
                  <p class="font-semibold mb-1 text-green-700">✅ Contoh Valid:</p>
                  <div class="flex flex-wrap gap-2 mb-2">
                    <code class="bg-green-50 text-green-700 px-2 py-1 rounded text-xs">siti-budi-2024</code>
                    <code class="bg-green-50 text-green-700 px-2 py-1 rounded text-xs">pernikahan-kami</code>
                    <code class="bg-green-50 text-green-700 px-2 py-1 rounded text-xs">wedding-jakarta</code>
                  </div>
                  <p class="font-semibold mb-1 text-red-700">❌ Contoh Tidak Valid:</p>
                  <div class="flex flex-wrap gap-2">
                    <code class="bg-red-50 text-red-700 px-2 py-1 rounded text-xs line-through">Siti Budi</code>
                    <code class="bg-red-50 text-red-700 px-2 py-1 rounded text-xs line-through">siti_budi</code>
                    <code class="bg-red-50 text-red-700 px-2 py-1 rounded text-xs line-through">sb</code>
                  </div>
                </div>
              </div>
            </div>
            
            <form @submit.prevent="updateSubdomain">
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Subdomain MyAkad</label>
                  <div class="flex items-center space-x-2">
                    <input
                      v-model="subdomainForm.subdomain"
                      type="text"
                      class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                      placeholder="nama-undangan"
                      required
                    />
                    <span class="text-gray-600">.myakad.test</span>
                  </div>
                  <p v-if="subdomainForm.errors.subdomain" class="text-red-600 text-sm mt-1">
                    {{ subdomainForm.errors.subdomain }}
                  </p>
                  <p class="text-xs text-gray-500 mt-2">
                    Hanya huruf kecil, angka, dan tanda hubung (-). Min 3 karakter.
                  </p>
                </div>

                <div class="flex items-center space-x-3">
                  <button
                    type="submit"
                    :disabled="subdomainForm.processing"
                    class="bg-pink-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-pink-700 transition disabled:opacity-50"
                  >
                    {{ subdomainForm.processing ? 'Menyimpan...' : 'Simpan Subdomain' }}
                  </button>
                  
                  <button
                    type="button"
                    @click="generateSubdomain"
                    class="bg-gray-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-gray-700 transition"
                  >
                    Generate Random
                  </button>
                </div>
              </div>
            </form>
          </div>

          <!-- Custom Domain -->
          <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center">
                <div>
                  <h2 class="text-xl font-bold text-gray-900">Custom Domain</h2>
                  <p class="text-sm text-gray-600 mt-1">Gunakan domain sendiri (opsional)</p>
                </div>
                <span class="ml-3 bg-purple-100 text-purple-700 text-xs font-semibold px-3 py-1 rounded-full">
                  Premium
                </span>
              </div>
              <button
                @click="showCustomDomainGuide = !showCustomDomainGuide"
                class="text-purple-600 hover:text-purple-700 text-sm font-medium flex items-center"
              >
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ showCustomDomainGuide ? 'Sembunyikan' : 'Panduan' }}
              </button>
            </div>

            <!-- Guide Section -->
            <div v-if="showCustomDomainGuide" class="mb-6 bg-gradient-to-r from-purple-50 to-pink-50 border border-purple-200 rounded-lg p-5">
              <h3 class="font-bold text-purple-900 mb-3 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                Panduan Custom Domain
              </h3>
              
              <div class="space-y-3 text-sm text-purple-900">
                <div>
                  <p class="font-semibold mb-1">🌐 Apa itu Custom Domain?</p>
                  <p class="text-purple-800">Custom domain memungkinkan Anda menggunakan domain sendiri untuk undangan. Contoh: <code class="bg-purple-100 px-2 py-0.5 rounded">undangan.example.com</code> atau <code class="bg-purple-100 px-2 py-0.5 rounded">wedding.mydomain.id</code></p>
                </div>

                <div>
                  <p class="font-semibold mb-1">⚙️ Cara Setup Custom Domain:</p>
                  <ol class="list-decimal list-inside text-purple-800 space-y-2 ml-2">
                    <li>
                      <strong>Beli domain</strong> dari provider seperti Niagahoster, Dewaweb, atau Namecheap
                    </li>
                    <li>
                      <strong>Login ke DNS Management</strong> di provider domain Anda
                    </li>
                    <li>
                      <strong>Buat CNAME record:</strong>
                      <div class="bg-white rounded p-2 mt-1 border border-purple-200">
                        <p class="text-xs">Type: <code class="bg-purple-100 px-1 rounded">CNAME</code></p>
                        <p class="text-xs">Name: <code class="bg-purple-100 px-1 rounded">undangan</code> (atau subdomain lain)</p>
                        <p class="text-xs">Value: <code class="bg-purple-100 px-1 rounded">myakad.test</code></p>
                      </div>
                    </li>
                    <li>
                      <strong>Tunggu propagasi DNS</strong> (5-30 menit, kadang sampai 24 jam)
                    </li>
                    <li>
                      <strong>Masukkan domain</strong> di form ini dan klik Simpan
                    </li>
                    <li>
                      <strong>Verifikasi</strong> dengan membuka domain Anda di browser
                    </li>
                  </ol>
                </div>

                <div>
                  <p class="font-semibold mb-1">📋 Format Domain yang Valid:</p>
                  <ul class="list-disc list-inside text-purple-800 space-y-1 ml-2">
                    <li><code class="bg-purple-100 px-1 rounded">undangan.example.com</code></li>
                    <li><code class="bg-purple-100 px-1 rounded">wedding.mydomain.id</code></li>
                    <li><code class="bg-purple-100 px-1 rounded">pernikahan.sitibudi.com</code></li>
                  </ul>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                  <p class="font-semibold text-yellow-800 mb-1">⚠️ Catatan Penting:</p>
                  <ul class="list-disc list-inside text-yellow-700 text-xs space-y-1 ml-2">
                    <li>Custom domain memerlukan paket Premium</li>
                    <li>Pastikan domain sudah aktif dan DNS sudah propagasi</li>
                    <li>Jika ada masalah, hubungi support kami</li>
                  </ul>
                </div>

                <div>
                  <p class="font-semibold mb-1">🔍 Troubleshooting:</p>
                  <div class="space-y-2 text-purple-800">
                    <div class="bg-white rounded p-2 border border-purple-200">
                      <p class="font-medium text-xs">❓ Domain tidak bisa diakses?</p>
                      <p class="text-xs">→ Tunggu propagasi DNS (bisa sampai 24 jam)</p>
                    </div>
                    <div class="bg-white rounded p-2 border border-purple-200">
                      <p class="font-medium text-xs">❓ Error "Domain sudah digunakan"?</p>
                      <p class="text-xs">→ Domain sudah dipakai user lain, gunakan subdomain berbeda</p>
                    </div>
                    <div class="bg-white rounded p-2 border border-purple-200">
                      <p class="font-medium text-xs">❓ Tidak punya domain?</p>
                      <p class="text-xs">→ Gunakan subdomain gratis MyAkad saja, sudah cukup!</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <form @submit.prevent="updateCustomDomain">
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Domain Kustom</label>
                  <input
                    v-model="customDomainForm.custom_domain"
                    type="text"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                    placeholder="undangan.example.com"
                  />
                  <p v-if="customDomainForm.errors.custom_domain" class="text-red-600 text-sm mt-1">
                    {{ customDomainForm.errors.custom_domain }}
                  </p>
                  <p class="text-xs text-gray-500 mt-2">
                    Contoh: undangan.example.com atau wedding.mydomain.id
                  </p>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                  <h4 class="font-semibold text-blue-900 mb-2">Cara Setup Custom Domain:</h4>
                  <ol class="text-sm text-blue-800 space-y-1 list-decimal list-inside">
                    <li>Buat CNAME record di DNS provider Anda</li>
                    <li>Arahkan ke: <code class="bg-blue-100 px-2 py-0.5 rounded">myakad.test</code></li>
                    <li>Tunggu propagasi DNS (5-30 menit)</li>
                    <li>Simpan domain di form ini</li>
                  </ol>
                </div>

                <button
                  type="submit"
                  :disabled="customDomainForm.processing"
                  class="bg-purple-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-purple-700 transition disabled:opacity-50"
                >
                  {{ customDomainForm.processing ? 'Menyimpan...' : 'Simpan Custom Domain' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
