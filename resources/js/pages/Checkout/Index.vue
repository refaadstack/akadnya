<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'

// Disable default layout
defineOptions({
  layout: undefined
})

interface Template {
  id: number
  slug: string
  name: string
  price: number
}

interface Product {
  id: number
  slug: string
  name: string
  description: string
  price: number
  is_recurring?: boolean
  recurring_interval?: string
}

const props = defineProps<{
  template: Template
  basePackages: Product[]
  addons: Product[]
}>()

const selectedBasePackage = ref<number | null>(null)
const selectedAddons = ref<number[]>([])
const previewData = ref<Record<string, any> | null>(null)
const isSubmitting = ref(false)
const showUserMenu = ref(false)

const logout = () => {
  router.post('/logout')
}

// Load preview data from sessionStorage
onMounted(() => {
  try {
    const STORAGE_KEY = `preview_data_${props.template.slug}`
    const stored = sessionStorage.getItem(STORAGE_KEY)
    if (stored) {
      const { data } = JSON.parse(stored)
      previewData.value = data
    }
  } catch (e) {
    console.error('Failed to load preview data:', e)
  }
  
  // Select first base package by default
  if (props.basePackages.length > 0) {
    selectedBasePackage.value = props.basePackages[0].id
  }
})

// Calculate total
const total = computed(() => {
  let sum = Number(props.template.price)
  
  // Add selected base package
  if (selectedBasePackage.value) {
    const basePackage = props.basePackages.find(p => p.id === selectedBasePackage.value)
    if (basePackage) {
      sum += Number(basePackage.price)
    }
  }
  
  // Add selected addons
  selectedAddons.value.forEach(addonId => {
    const addon = props.addons.find(a => a.id === addonId)
    if (addon) {
      sum += Number(addon.price)
    }
  })
  
  return sum
})

// Get selected base package
const selectedBasePackageData = computed(() => {
  if (!selectedBasePackage.value) return null
  return props.basePackages.find(p => p.id === selectedBasePackage.value)
})

// Toggle addon selection
const toggleAddon = (addonId: number) => {
  const index = selectedAddons.value.indexOf(addonId)
  if (index > -1) {
    selectedAddons.value.splice(index, 1)
  } else {
    selectedAddons.value.push(addonId)
  }
}

// Submit order
const submitOrder = async () => {
  if (isSubmitting.value) return
  
  if (!selectedBasePackage.value) {
    alert('Silakan pilih paket terlebih dahulu')
    return
  }
  
  isSubmitting.value = true
  
  try {
    // Get CSRF token from cookie (always available in Laravel)
    const xsrfToken = decodeURIComponent(
      document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] ?? ''
    )

    const response = await fetch('/checkout', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-XSRF-TOKEN': xsrfToken,
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({
        template_id: props.template.id,
        base_package_id: selectedBasePackage.value,
        addon_ids: selectedAddons.value,
        preview_data: previewData.value,
      }),
    })
    
    const data = await response.json()
    
    if (!response.ok) {
      // Show validation errors or server error message
      const message = data.message || (data.errors ? Object.values(data.errors).flat().join('\n') : 'Terjadi kesalahan saat membuat order.')
      alert(message)
      isSubmitting.value = false
      return
    }
    
    if (data.snap_token) {
      const snapToken = data.snap_token
      
      // @ts-ignore - Midtrans Snap global
      window.snap.pay(snapToken, {
        onSuccess: function(result: any) {
          console.log('Payment success:', result)
          router.visit('/dashboard')
        },
        onPending: function(result: any) {
          console.log('Payment pending:', result)
          alert('Menunggu pembayaran. Silakan selesaikan pembayaran Anda.')
          router.visit('/dashboard')
        },
        onError: function(result: any) {
          console.log('Payment error:', result)
          alert('Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.')
          isSubmitting.value = false
        },
        onClose: function() {
          console.log('Payment popup closed')
          isSubmitting.value = false
        }
      })
    } else {
      alert('Gagal mendapatkan token pembayaran. Silakan coba lagi.')
      isSubmitting.value = false
    }
  } catch (error) {
    console.error('Checkout error:', error)
    alert('Terjadi kesalahan koneksi. Silakan coba lagi.')
    isSubmitting.value = false
  }
}
</script>

<template>
  <div>
    <Head title="Checkout" />

    <div class="min-h-screen bg-gray-50">
      <!-- Navigation -->
      <nav class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-4">
          <div class="flex items-center justify-between h-16">
            <!-- Logo -->
            <Link href="/dashboard" class="flex items-center space-x-2">
              <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-purple-600 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-xl">M</span>
              </div>
              <span class="text-2xl font-bold bg-gradient-to-r from-pink-600 to-purple-600 bg-clip-text text-transparent">
                MyAkad
              </span>
            </Link>

            <!-- Navigation Links -->
            <div class="hidden md:flex items-center space-x-8">
              <Link
                href="/dashboard"
                class="text-gray-700 hover:text-pink-600 font-medium transition"
              >
                Dashboard
              </Link>
              <Link
                href="/templates"
                class="text-gray-700 hover:text-pink-600 font-medium transition"
              >
                Template
              </Link>
            </div>

            <!-- User Menu -->
            <div class="relative">
              <button
                @click="showUserMenu = !showUserMenu"
                class="flex items-center space-x-3 focus:outline-none"
              >
                <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-purple-600 rounded-full flex items-center justify-center">
                  <span class="text-white font-semibold text-sm">
                    {{ $page.props.auth?.user?.name?.charAt(0).toUpperCase() || 'U' }}
                  </span>
                </div>
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              <!-- Dropdown Menu -->
              <div
                v-show="showUserMenu"
                @click="showUserMenu = false"
                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50"
              >
                <div class="px-4 py-2 border-b border-gray-100">
                  <p class="text-sm font-semibold text-gray-900">{{ $page.props.auth?.user?.name }}</p>
                  <p class="text-xs text-gray-600">{{ $page.props.auth?.user?.email }}</p>
                </div>
                
                <Link
                  href="/settings/profile"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition"
                >
                  <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Profil
                  </div>
                </Link>

                <Link
                  href="/settings/security"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition"
                >
                  <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Keamanan
                  </div>
                </Link>

                <div class="border-t border-gray-100 mt-2 pt-2">
                  <button
                    @click="logout"
                    class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition"
                  >
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

    <div class="min-h-screen bg-gradient-to-br from-pink-50 via-white to-purple-50 py-8">
      <div class="container mx-auto px-4 max-w-6xl">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Checkout</h1>

        <div class="grid lg:grid-cols-3 gap-8">
        <!-- Order Summary -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Template -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Template Terpilih</h2>
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold text-gray-800">{{ template.name }}</h3>
                <p class="text-sm text-gray-600">Template undangan digital</p>
              </div>
              <div class="text-right">
                <p class="text-2xl font-bold text-pink-600">
                  Rp {{ template.price.toLocaleString('id-ID') }}
                </p>
              </div>
            </div>
          </div>

          <!-- Base Package -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Pilih Paket</h2>
            <div class="space-y-3">
              <div
                v-for="pkg in basePackages"
                :key="pkg.id"
                @click="selectedBasePackage = pkg.id"
                class="border-2 rounded-lg p-4 cursor-pointer transition-all"
                :class="selectedBasePackage === pkg.id 
                  ? 'border-pink-600 bg-pink-50' 
                  : 'border-gray-200 hover:border-gray-300'"
              >
                <div class="flex items-start justify-between">
                  <div class="flex items-start flex-1">
                    <input
                      type="radio"
                      :checked="selectedBasePackage === pkg.id"
                      class="mt-1 mr-3"
                      @change="selectedBasePackage = pkg.id"
                    />
                    <div class="flex-1">
                      <h3 class="font-semibold text-gray-800">{{ pkg.name }}</h3>
                      <p class="text-sm text-gray-600 mt-1">{{ pkg.description }}</p>
                      <div class="mt-3 space-y-1">
                        <p class="text-sm text-gray-700">✓ Undangan digital {{ pkg.is_recurring ? 'berlangganan' : 'selamanya' }}</p>
                        <p class="text-sm text-gray-700">✓ Subdomain gratis (namaanda.{{ $page.props.appDomain }})</p>
                        <p class="text-sm text-gray-700">✓ Unlimited tamu</p>
                        <p class="text-sm text-gray-700">✓ RSVP & ucapan</p>
                        <p class="text-sm text-gray-700">✓ Galeri foto</p>
                      </div>
                    </div>
                  </div>
                  <div class="text-right ml-4">
                    <p class="text-2xl font-bold text-gray-900">
                      Rp {{ pkg.price.toLocaleString('id-ID') }}
                    </p>
                    <p v-if="pkg.is_recurring" class="text-xs text-gray-500 mt-1">
                      /{{ pkg.recurring_interval === 'monthly' ? 'bulan' : 'tahun' }}
                    </p>
                    <p v-else class="text-xs text-gray-500 mt-1">Sekali bayar</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Add-ons -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Tambahan (Opsional)</h2>
            <div class="space-y-4">
              <div
                v-for="addon in addons"
                :key="addon.id"
                @click="toggleAddon(addon.id)"
                class="border-2 rounded-lg p-4 cursor-pointer transition-all"
                :class="selectedAddons.includes(addon.id) 
                  ? 'border-pink-600 bg-pink-50' 
                  : 'border-gray-200 hover:border-gray-300'"
              >
                <div class="flex items-start justify-between">
                  <div class="flex items-start flex-1">
                    <input
                      type="checkbox"
                      :checked="selectedAddons.includes(addon.id)"
                      class="mt-1 mr-3"
                      @change="toggleAddon(addon.id)"
                    />
                    <div class="flex-1">
                      <h3 class="font-semibold text-gray-800">{{ addon.name }}</h3>
                      <p class="text-sm text-gray-600 mt-1">{{ addon.description }}</p>
                    </div>
                  </div>
                  <div class="text-right ml-4">
                    <p class="text-lg font-bold text-gray-900">
                      Rp {{ addon.price.toLocaleString('id-ID') }}
                    </p>
                    <p v-if="addon.is_recurring" class="text-xs text-gray-500">
                      /{{ addon.recurring_interval }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Preview Data Info -->
          <div v-if="previewData" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
              <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
              </svg>
              <div>
                <p class="text-sm font-semibold text-blue-900">Data preview Anda tersimpan</p>
                <p class="text-sm text-blue-700 mt-1">
                  Data yang Anda input di halaman preview akan otomatis digunakan untuk undangan Anda setelah pembayaran berhasil.
                </p>
              </div>
            </div>
          </div>

          <div v-else class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-start">
              <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
              </svg>
              <div>
                <p class="text-sm font-semibold text-yellow-900">Belum ada data preview</p>
                <p class="text-sm text-yellow-700 mt-1">
                  Anda bisa mengisi data undangan nanti setelah pembayaran, atau kembali ke halaman preview untuk mengisi data terlebih dahulu.
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Order Total & Payment -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-lg shadow-md p-6 sticky top-24">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Ringkasan Order</h2>
            
            <div class="space-y-3 mb-4">
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Template {{ template.name }}</span>
                <span class="font-medium">Rp {{ template.price.toLocaleString('id-ID') }}</span>
              </div>
              
              <div v-if="selectedBasePackageData" class="flex justify-between text-sm">
                <span class="text-gray-600">{{ selectedBasePackageData.name }}</span>
                <span class="font-medium">Rp {{ selectedBasePackageData.price.toLocaleString('id-ID') }}</span>
              </div>

              <div
                v-for="addonId in selectedAddons"
                :key="addonId"
                class="flex justify-between text-sm"
              >
                <span class="text-gray-600">{{ addons.find(a => a.id === addonId)?.name }}</span>
                <span class="font-medium">Rp {{ addons.find(a => a.id === addonId)?.price.toLocaleString('id-ID') }}</span>
              </div>
            </div>

            <div class="border-t pt-4 mb-6">
              <div class="flex justify-between items-center">
                <span class="text-lg font-bold text-gray-900">Total</span>
                <span class="text-2xl font-bold text-pink-600">
                  Rp {{ total.toLocaleString('id-ID') }}
                </span>
              </div>
            </div>

            <button
              @click="submitOrder"
              :disabled="isSubmitting"
              class="w-full bg-pink-600 text-white py-3 rounded-lg font-semibold hover:bg-pink-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ isSubmitting ? 'Memproses...' : 'Lanjut ke Pembayaran' }}
            </button>

            <p class="text-xs text-gray-500 text-center mt-4">
              Dengan melanjutkan, Anda menyetujui syarat dan ketentuan kami
            </p>
          </div>
        </div>
        </div>
      </div>
    </div>
    </div>
  </div>
</template>
