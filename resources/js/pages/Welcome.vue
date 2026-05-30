<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import PublicNavbar from '@/components/PublicNavbar.vue'
import PublicFooter from '@/components/PublicFooter.vue'

interface BasePackage {
  name: string
  price: number
  description: string
}

const props = defineProps<{
  canRegister: boolean
  basePackage: BasePackage | null
}>()

// Check if in local environment
const isLocal = import.meta.env.DEV

// Format price to IDR
const formattedPrice = computed(() => {
  if (!props.basePackage) return 'Rp 0'
  return `Rp ${props.basePackage.price.toLocaleString('id-ID')}`
})
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-pink-50 via-white to-purple-50">
    <Head title="MyAkad - Undangan Digital Pernikahan" />

    <!-- Navigation -->
    <PublicNavbar :can-register="canRegister" current-page="home" />

    <!-- Hero Section -->
    <section class="pt-32 pb-20 px-4">
      <div class="container mx-auto max-w-6xl">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
          <div>
            <h1 class="text-5xl lg:text-6xl font-bold text-gray-900 leading-tight mb-6">
              Undangan Digital
              <span class="bg-gradient-to-r from-pink-600 to-purple-600 bg-clip-text text-transparent">
                Pernikahan Impian
              </span>
            </h1>
            <p class="text-xl text-gray-600 mb-8">
              Buat undangan pernikahan digital yang elegan dan modern. Bagikan momen bahagia Anda dengan cara yang berkesan.
            </p>
            <div class="flex flex-wrap gap-4">
              <Link
                href="/templates"
                class="bg-gradient-to-r from-pink-600 to-purple-600 text-white px-8 py-4 rounded-full font-semibold text-lg hover:shadow-xl transition transform hover:scale-105"
              >
                Lihat Template
              </Link>
              <a
                href="#features"
                class="border-2 border-gray-300 text-gray-700 px-8 py-4 rounded-full font-semibold text-lg hover:border-pink-600 hover:text-pink-600 transition"
              >
                Pelajari Lebih Lanjut
              </a>
            </div>
          </div>

          <div class="relative">
            <div class="relative z-10 bg-white rounded-2xl shadow-2xl p-8 transform rotate-2">
              <div class="bg-gradient-to-br from-pink-100 to-purple-100 rounded-xl p-8 text-center">
                <div class="w-24 h-24 bg-white rounded-full mx-auto mb-4 flex items-center justify-center">
                  <svg class="w-12 h-12 text-pink-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                  </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Sarah & John</h3>
                <p class="text-gray-600 mb-4">25 Desember 2026</p>
                <div class="bg-white rounded-lg p-4 text-sm text-gray-700">
                  "Dengan memohon rahmat dan ridho Allah SWT, kami mengundang Bapak/Ibu/Saudara/i untuk hadir di acara pernikahan kami"
                </div>
              </div>
            </div>
            <div class="absolute inset-0 bg-gradient-to-br from-pink-400 to-purple-400 rounded-2xl transform -rotate-3"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 px-4 bg-white">
      <div class="container mx-auto max-w-6xl">
        <div class="text-center mb-16">
          <h2 class="text-4xl font-bold text-gray-900 mb-4">Fitur Unggulan</h2>
          <p class="text-xl text-gray-600">Semua yang Anda butuhkan untuk undangan digital sempurna</p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
          <div class="text-center p-6">
            <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-purple-600 rounded-2xl mx-auto mb-4 flex items-center justify-center">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Template Elegan</h3>
            <p class="text-gray-600">Pilihan template modern dan elegan yang dapat disesuaikan dengan tema pernikahan Anda</p>
          </div>

          <div class="text-center p-6">
            <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-purple-600 rounded-2xl mx-auto mb-4 flex items-center justify-center">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Manajemen Tamu</h3>
            <p class="text-gray-600">Kelola daftar tamu, kirim undangan personal, dan lacak konfirmasi kehadiran dengan mudah</p>
          </div>

          <div class="text-center p-6">
            <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-purple-600 rounded-2xl mx-auto mb-4 flex items-center justify-center">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Galeri Foto</h3>
            <p class="text-gray-600">Tampilkan foto-foto indah perjalanan cinta Anda dalam galeri yang menarik</p>
          </div>

          <div class="text-center p-6">
            <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-purple-600 rounded-2xl mx-auto mb-4 flex items-center justify-center">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Countdown Timer</h3>
            <p class="text-gray-600">Hitung mundur hari spesial Anda dengan timer yang menarik dan interaktif</p>
          </div>

          <div class="text-center p-6">
            <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-purple-600 rounded-2xl mx-auto mb-4 flex items-center justify-center">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">RSVP Online</h3>
            <p class="text-gray-600">Tamu dapat konfirmasi kehadiran langsung melalui undangan digital</p>
          </div>

          <div class="text-center p-6">
            <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-purple-600 rounded-2xl mx-auto mb-4 flex items-center justify-center">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Amplop Digital</h3>
            <p class="text-gray-600">Terima hadiah pernikahan secara digital dengan informasi rekening bank</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Pricing Section -->
    <section class="py-20 px-4 bg-gradient-to-br from-pink-50 to-purple-50">
      <div class="container mx-auto max-w-6xl">
        <div class="text-center mb-16">
          <h2 class="text-4xl font-bold text-gray-900 mb-4">Harga Terjangkau</h2>
          <p class="text-xl text-gray-600">
            {{ basePackage ? `Mulai dari ${formattedPrice} untuk undangan digital seumur hidup` : 'Harga akan segera tersedia' }}
          </p>
        </div>

        <div v-if="basePackage" class="max-w-md mx-auto bg-white rounded-2xl shadow-xl p-8">
          <div class="text-center mb-6">
            <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ basePackage.name }}</h3>
            <div class="text-5xl font-bold text-pink-600 mb-2">{{ formattedPrice }}</div>
            <p class="text-gray-600">Sekali bayar, seumur hidup</p>
          </div>

          <ul class="space-y-4 mb-8">
            <li class="flex items-start">
              <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span class="text-gray-700">Pilih template favorit</span>
            </li>
            <li class="flex items-start">
              <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span class="text-gray-700">Subdomain gratis (namaanda.{{ $page.props.appDomain }})</span>
            </li>
            <li class="flex items-start">
              <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span class="text-gray-700">Unlimited tamu</span>
            </li>
            <li class="flex items-start">
              <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span class="text-gray-700">RSVP & ucapan</span>
            </li>
            <li class="flex items-start">
              <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span class="text-gray-700">Galeri foto</span>
            </li>
            <li class="flex items-start">
              <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span class="text-gray-700">Amplop digital</span>
            </li>
          </ul>

          <Link
            href="/templates"
            class="block w-full bg-gradient-to-r from-pink-600 to-purple-600 text-white text-center py-4 rounded-full font-semibold text-lg hover:shadow-xl transition transform hover:scale-105"
          >
            Mulai Sekarang
          </Link>
        </div>

        <div v-else class="max-w-md mx-auto bg-white rounded-2xl shadow-xl p-8 text-center">
          <p class="text-gray-600 mb-4">Produk sedang dalam persiapan</p>
          <Link
            href="/templates"
            class="inline-block bg-gradient-to-r from-pink-600 to-purple-600 text-white px-8 py-4 rounded-full font-semibold text-lg hover:shadow-xl transition transform hover:scale-105"
          >
            Lihat Template
          </Link>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 px-4 bg-gradient-to-r from-pink-600 to-purple-600">
      <div class="container mx-auto max-w-4xl text-center">
        <h2 class="text-4xl font-bold text-white mb-6">
          Siap Membuat Undangan Digital Anda?
        </h2>
        <p class="text-xl text-pink-100 mb-8">
          Bergabunglah dengan ribuan pasangan yang telah mempercayai MyAkad untuk hari spesial mereka
        </p>
        <Link
          href="/templates"
          class="inline-block bg-white text-pink-600 px-8 py-4 rounded-full font-semibold text-lg hover:shadow-xl transition transform hover:scale-105"
        >
          Lihat Template Sekarang
        </Link>
      </div>
    </section>

    <!-- Footer -->
    <PublicFooter />

    <!-- Dev Tools Floating Button (only in local) -->
    <Link
      v-if="isLocal"
      href="/dev/payment-simulator"
      class="fixed bottom-6 right-6 bg-gradient-to-r from-orange-500 to-red-600 text-white p-4 rounded-full shadow-2xl hover:shadow-3xl transition transform hover:scale-110 z-50 group"
      title="Payment Simulator (Dev Tool)"
    >
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <span class="absolute right-full mr-3 top-1/2 -translate-y-1/2 bg-gray-900 text-white text-xs px-3 py-1 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition pointer-events-none">
        Payment Simulator
      </span>
    </Link>
  </div>
</template>
