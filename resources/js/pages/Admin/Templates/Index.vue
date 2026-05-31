<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'

defineOptions({
  layout: AdminLayout
})

interface Template {
  id: number
  slug: string
  name: string
  thumbnail_url: string | null
  version: string
  is_free: boolean
  price: number
  is_active: boolean
  sections_count: number
  ornaments_count: number
  synced_at: string | null
  created_at: string
}

defineProps<{
  templates: {
    data: Template[]
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}>()

const deleteTemplate = (id: number) => {
  if (confirm('Yakin ingin menghapus template ini?')) {
    router.delete(`/admin/templates/${id}`)
  }
}

const toggleActive = (id: number) => {
  router.post(`/admin/templates/${id}/toggle-active`)
}

const syncTemplates = () => {
  if (confirm('Sync semua template dari storage/templates/ ke database?')) {
    router.post('/admin/templates/sync')
  }
}
</script>

<template>
  <div>
    <Head title="Kelola Template - Admin" />

    <div class="min-h-screen bg-gray-50">
      <!-- Header -->
      <div class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-4 py-6">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">Kelola Template</h1>
              <p class="text-gray-600 mt-1">Manage wedding invitation templates</p>
            </div>
            <div class="flex items-center space-x-3">
              <button
                @click="syncTemplates"
                class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition flex items-center"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Sync Templates
              </button>
              <Link
                href="/admin/templates/upload"
                class="bg-gradient-to-r from-pink-600 to-purple-600 text-white px-6 py-3 rounded-lg font-semibold hover:shadow-lg transition flex items-center"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                Upload Template
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="container mx-auto px-4 py-8">
        <!-- Flash Messages -->
        <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
          {{ $page.props.flash.success }}
        </div>

        <!-- Templates Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="template in templates.data"
            :key="template.id"
            class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition"
          >
            <!-- Thumbnail -->
            <div class="relative h-48 bg-gradient-to-br from-pink-100 to-purple-100">
              <img
                v-if="template.thumbnail_url"
                :src="template.thumbnail_url"
                :alt="template.name"
                class="w-full h-full object-cover"
              />
              <div v-else class="w-full h-full flex items-center justify-center">
                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>

              <!-- Status Badge -->
              <div class="absolute top-3 right-3">
                <span
                  v-if="template.is_active"
                  class="bg-green-500 text-white text-xs font-semibold px-3 py-1 rounded-full"
                >
                  Aktif
                </span>
                <span
                  v-else
                  class="bg-gray-500 text-white text-xs font-semibold px-3 py-1 rounded-full"
                >
                  Nonaktif
                </span>
              </div>

              <!-- Price Badge -->
              <div class="absolute top-3 left-3">
                <span
                  v-if="template.is_free"
                  class="bg-blue-500 text-white text-xs font-semibold px-3 py-1 rounded-full"
                >
                  Gratis
                </span>
                <span
                  v-else
                  class="bg-purple-600 text-white text-xs font-semibold px-3 py-1 rounded-full"
                >
                  Rp {{ template.price.toLocaleString('id-ID') }}
                </span>
              </div>
            </div>

            <!-- Content -->
            <div class="p-5">
              <h3 class="text-xl font-bold text-gray-900 mb-2">{{ template.name }}</h3>
              <p class="text-sm text-gray-600 mb-4">
                <span class="font-medium">Slug:</span> {{ template.slug }}
              </p>

              <!-- Stats -->
              <div class="flex items-center space-x-4 text-sm text-gray-600 mb-4">
                <div class="flex items-center">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                  </svg>
                  {{ template.sections_count }} sections
                </div>
                <div class="flex items-center">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                  </svg>
                  {{ template.ornaments_count }} ornaments
                </div>
              </div>

              <div class="text-xs text-gray-500 mb-4">
                <div>Version: {{ template.version }}</div>
                <div>Created: {{ template.created_at }}</div>
              </div>

              <!-- Actions -->
              <div class="flex flex-col space-y-2">
                <a
                  :href="`/templates/${template.slug}/render`"
                  target="_blank"
                  class="bg-green-600 text-white text-center px-4 py-2 rounded-lg text-sm font-semibold hover:bg-green-700 transition"
                >
                  👁️ Preview
                </a>
                <div class="flex items-center space-x-2">
                  <Link
                    :href="`/admin/templates/${template.id}/edit`"
                    class="flex-1 bg-blue-600 text-white text-center px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition"
                  >
                    Edit
                  </Link>
                  <button
                    @click="toggleActive(template.id)"
                    class="flex-1 bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-700 transition"
                  >
                    {{ template.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                  </button>
                  <button
                    @click="deleteTemplate(template.id)"
                    class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-700 transition"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="templates.data.length === 0" class="text-center py-16">
          <svg class="w-24 h-24 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
          </svg>
          <h3 class="text-xl font-semibold text-gray-900 mb-2">Belum ada template</h3>
          <p class="text-gray-600 mb-6">Mulai dengan menambahkan template pertama Anda</p>
          <Link
            href="/admin/templates/create"
            class="inline-block bg-gradient-to-r from-pink-600 to-purple-600 text-white px-6 py-3 rounded-lg font-semibold hover:shadow-lg transition"
          >
            + Tambah Template
          </Link>
        </div>

        <!-- Pagination -->
        <div v-if="templates.last_page > 1" class="mt-8 flex justify-center">
          <div class="flex items-center space-x-2">
            <Link
              v-for="page in templates.last_page"
              :key="page"
              :href="`/admin/templates?page=${page}`"
              class="px-4 py-2 rounded-lg font-semibold transition"
              :class="page === templates.current_page ? 'bg-pink-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
            >
              {{ page }}
            </Link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
