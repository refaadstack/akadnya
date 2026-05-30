<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'

interface Template {
  id: number
  slug: string
  name: string
  thumbnail_url: string | null
  price: number
  is_free: boolean
}

defineProps<{
  templates: Template[]
}>()

// Disable default layout for this page
defineOptions({
  layout: undefined
})

const addingToCart = ref<number | null>(null)

const addToCart = (template: Template) => {
  addingToCart.value = template.id
  
  // Store template in session/localStorage for checkout
  sessionStorage.setItem('selected_template', JSON.stringify({
    id: template.id,
    slug: template.slug,
    name: template.name,
    price: template.price,
    is_free: template.is_free
  }))
  
  // Redirect to checkout with template query parameter
  setTimeout(() => {
    router.visit(`/checkout?template=${template.slug}`)
  }, 300)
}

</script>

<template>
  <div class="min-h-screen bg-gradient-to-b from-gray-50 to-white">
    <Head title="Pilih Template" />

    <!-- Header -->
    <header class="bg-white shadow-sm">
      <div class="container mx-auto px-4 py-6">
        <div class="flex items-center justify-between">
          <h1 class="text-3xl font-bold text-gray-900">Pilih Template Undangan</h1>
          <Link href="/" class="text-pink-600 hover:text-pink-700">
            Kembali ke Beranda
          </Link>
        </div>
      </div>
    </header>

    <!-- Templates Grid -->
    <main class="container mx-auto px-4 py-12">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div
          v-for="template in templates"
          :key="template.id"
          class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-100"
        >
          <!-- Thumbnail -->
          <div class="aspect-[3/4] bg-gradient-to-br from-pink-100 to-purple-100 relative">
            <img
              v-if="template.thumbnail_url"
              :src="template.thumbnail_url"
              :alt="template.name"
              class="w-full h-full object-cover"
            />
            <div
              v-else
              class="w-full h-full flex items-center justify-center text-6xl"
            >
              💐
            </div>
            
            <!-- Free Badge -->
            <div
              v-if="template.is_free"
              class="absolute top-4 right-4 bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold"
            >
              Gratis
            </div>
          </div>

          <!-- Content -->
          <div class="p-6">
            <h3 class="text-2xl font-serif font-bold text-gray-900 mb-2">
              {{ template.name }}
            </h3>
            
            <div class="flex items-center justify-between mb-6">
              <span class="text-2xl font-bold text-pink-600">
                {{ template.is_free ? 'Gratis' : `Rp ${template.price.toLocaleString('id-ID')}` }}
              </span>
              <span class="text-sm text-gray-500">
                Template Premium
              </span>
            </div>

            <!-- Features List -->
            <div class="mb-6 space-y-2">
              <div class="flex items-center text-sm text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Responsive & Mobile-Friendly
              </div>
              <div class="flex items-center text-sm text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Customizable Content
              </div>
              <div class="flex items-center text-sm text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                RSVP & Guest Management
              </div>
            </div>

            <!-- Actions -->
            <div class="space-y-3">
              <!-- Preview Button -->
              <Link
                :href="`/templates/${template.slug}/preview`"
                class="block w-full bg-white border-2 border-pink-600 text-pink-600 text-center py-3 rounded-lg font-semibold hover:bg-pink-50 transition-all duration-200 transform hover:scale-105"
              >
                <span class="flex items-center justify-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  Lihat Preview
                </span>
              </Link>

              <!-- Add to Cart Button -->
              <button
                @click="addToCart(template)"
                :disabled="addingToCart === template.id"
                class="block w-full bg-gradient-to-r from-pink-600 to-purple-600 text-white text-center py-3 rounded-lg font-semibold hover:from-pink-700 hover:to-purple-700 transition-all duration-200 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none shadow-lg hover:shadow-xl"
              >
                <span v-if="addingToCart === template.id" class="flex items-center justify-center gap-2">
                  <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Memproses...
                </span>
                <span v-else class="flex items-center justify-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                  Pilih Template Ini
                </span>
              </button>
            </div>

            <!-- Info Text -->
            <p class="text-xs text-center text-gray-500 mt-3">
              💡 Klik "Lihat Preview" untuk coba dengan data Anda
            </p>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div
        v-if="templates.length === 0"
        class="text-center py-16"
      >
        <p class="text-gray-500 text-lg">Belum ada template tersedia</p>
      </div>
    </main>
  </div>
</template>
