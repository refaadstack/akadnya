<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'

interface Template {
  id: number
  slug: string
  name: string
  thumbnail_url: string | null
}

interface Props {
  template: Template
}

const props = defineProps<Props>()

const renderUrl = computed(() => `/templates/${props.template.slug}/render`)
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <Head :title="`Preview: ${template.name}`" />

    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
      <div class="container mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <Link
              href="/templates"
              class="text-gray-600 hover:text-gray-900 flex items-center"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              Kembali
            </Link>
            <div class="h-6 w-px bg-gray-300"></div>
            <h1 class="text-xl font-semibold text-gray-900">
              Preview: {{ template.name }}
            </h1>
          </div>

          <div class="flex items-center space-x-3">
            <Link
              href="/checkout"
              class="bg-gradient-to-r from-pink-600 to-purple-600 text-white px-6 py-2 rounded-lg font-semibold hover:shadow-lg transition"
            >
              Pilih Template
            </Link>
          </div>
        </div>
      </div>
    </header>

    <!-- Preview Content -->
    <main class="container mx-auto px-4 py-8">
      <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Device Frame (Optional) -->
        <div class="bg-gray-100 p-4 border-b">
          <div class="flex items-center justify-center space-x-2">
            <div class="flex space-x-1">
              <div class="w-3 h-3 rounded-full bg-red-500"></div>
              <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
              <div class="w-3 h-3 rounded-full bg-green-500"></div>
            </div>
            <div class="flex-1 max-w-md mx-4">
              <div class="bg-white rounded px-3 py-1 text-sm text-gray-600 text-center">
                {{ renderUrl }}
              </div>
            </div>
          </div>
        </div>

        <!-- Iframe Preview -->
        <div class="relative" style="height: calc(100vh - 250px); min-height: 600px;">
          <iframe
            :src="renderUrl"
            class="w-full h-full border-0"
            title="Template Preview"
            sandbox="allow-same-origin allow-scripts allow-forms"
          ></iframe>
        </div>
      </div>

      <!-- Template Info -->
      <div class="mt-6 bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Tentang Template</h2>
        <div class="grid md:grid-cols-2 gap-6">
          <div>
            <h3 class="text-sm font-medium text-gray-500 mb-2">Nama Template</h3>
            <p class="text-gray-900">{{ template.name }}</p>
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-500 mb-2">Slug</h3>
            <p class="text-gray-900 font-mono text-sm">{{ template.slug }}</p>
          </div>
        </div>

        <div class="mt-6 pt-6 border-t">
          <p class="text-sm text-gray-600 mb-4">
            Preview ini menampilkan template dengan data dummy. Setelah Anda memilih template, 
            Anda dapat mengedit konten sesuai keinginan Anda.
          </p>
          <Link
            href="/checkout"
            class="inline-block bg-gradient-to-r from-pink-600 to-purple-600 text-white px-8 py-3 rounded-lg font-semibold hover:shadow-lg transition"
          >
            Pilih Template Ini
          </Link>
        </div>
      </div>
    </main>
  </div>
</template>
