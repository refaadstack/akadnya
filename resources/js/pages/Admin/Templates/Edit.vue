<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'

defineOptions({
  layout: AdminLayout
})

interface Section {
  id?: number
  file: string
  label: string
  sort_order: number
  is_required: boolean
}

interface Ornament {
  id?: number
  file: string
  label: string
  position: string
}

interface Template {
  id: number
  slug: string
  name: string
  thumbnail_url: string | null
  version: string
  is_free: boolean
  price: number
  is_active: boolean
  sections: Section[]
  ornaments: Ornament[]
}

const props = defineProps<{
  template: Template
}>()

const form = useForm({
  name: props.template.name,
  slug: props.template.slug,
  thumbnail: null as File | null,
  version: props.template.version,
  is_free: props.template.is_free,
  price: props.template.price,
  is_active: props.template.is_active,
  sections: [...props.template.sections] as Section[],
  ornaments: [...props.template.ornaments] as Ornament[],
})

const thumbnailPreview = ref<string | null>(props.template.thumbnail_url)

const handleThumbnailChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) {
    form.thumbnail = file
    thumbnailPreview.value = URL.createObjectURL(file)
  }
}

const addSection = () => {
  const nextOrder = form.sections.length + 1
  form.sections.push({
    file: '',
    label: '',
    sort_order: nextOrder,
    is_required: false,
  })
}

const removeSection = (index: number) => {
  form.sections.splice(index, 1)
  // Reorder
  form.sections.forEach((section, idx) => {
    section.sort_order = idx + 1
  })
}

const addOrnament = () => {
  form.ornaments.push({
    file: '',
    label: '',
    position: 'top',
  })
}

const removeOrnament = (index: number) => {
  form.ornaments.splice(index, 1)
}

const submit = () => {
  // Use POST with _method spoofing for file uploads
  form.post(`/admin/templates/${props.template.id}`, {
    preserveScroll: true,
    forceFormData: true,
    onBefore: () => {
      // Add _method field for Laravel method spoofing
      form.transform((data) => ({
        ...data,
        _method: 'PUT',
      }))
    },
  })
}
</script>

<template>
  <div>
    <Head :title="`Edit ${template.name} - Admin`" />

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
              <h1 class="text-3xl font-bold text-gray-900">Edit Template: {{ template.name }}</h1>
              <p class="text-gray-600 mt-1">Update template undangan</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Form -->
      <div class="container mx-auto px-4 py-8">
        <!-- Flash Messages -->
        <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
          {{ $page.props.flash.success }}
        </div>
        <div v-if="$page.props.errors?.error" class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
          {{ $page.props.errors.error }}
        </div>

        <form @submit.prevent="submit" class="max-w-4xl">
          <!-- Basic Info -->
          <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Informasi Dasar</h2>
            
            <div class="space-y-4">
              <div class="grid md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Nama Template *</label>
                  <input
                    v-model="form.name"
                    type="text"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                    placeholder="Contoh: Adat Jawa"
                    required
                  />
                  <p v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Slug</label>
                  <input
                    v-model="form.slug"
                    type="text"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                    placeholder="adat-jawa"
                  />
                  <p v-if="form.errors.slug" class="text-red-600 text-sm mt-1">{{ form.errors.slug }}</p>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Thumbnail</label>
                <div class="flex items-start space-x-4">
                  <div v-if="thumbnailPreview" class="w-48 h-32 rounded-lg overflow-hidden border-2 border-gray-200">
                    <img :src="thumbnailPreview" alt="Preview" class="w-full h-full object-cover" />
                  </div>
                  <div class="flex-1">
                    <input
                      type="file"
                      @change="handleThumbnailChange"
                      accept="image/*"
                      class="hidden"
                      id="thumbnail-upload"
                    />
                    <label
                      for="thumbnail-upload"
                      class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition"
                    >
                      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      Ganti Gambar
                    </label>
                  </div>
                </div>
              </div>

              <div class="grid md:grid-cols-3 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Version</label>
                  <input
                    v-model="form.version"
                    type="text"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                    placeholder="1.0.0"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Harga</label>
                  <input
                    v-model.number="form.price"
                    type="number"
                    :disabled="form.is_free"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent disabled:bg-gray-100"
                    placeholder="0"
                  />
                </div>

                <div class="flex items-center space-x-4 pt-8">
                  <label class="flex items-center">
                    <input v-model="form.is_free" type="checkbox" class="mr-2" />
                    <span class="text-sm font-medium text-gray-700">Gratis</span>
                  </label>
                  <label class="flex items-center">
                    <input v-model="form.is_active" type="checkbox" class="mr-2" />
                    <span class="text-sm font-medium text-gray-700">Aktif</span>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- Sections -->
          <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-xl font-bold text-gray-900">Sections *</h2>
              <button
                type="button"
                @click="addSection"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition"
              >
                + Tambah Section
              </button>
            </div>

            <div class="space-y-4">
              <div
                v-for="(section, index) in form.sections"
                :key="index"
                class="border border-gray-200 rounded-lg p-4"
              >
                <div class="flex items-start justify-between mb-3">
                  <span class="text-sm font-semibold text-gray-700">Section #{{ index + 1 }}</span>
                  <button
                    v-if="form.sections.length > 1"
                    type="button"
                    @click="removeSection(index)"
                    class="text-red-600 hover:text-red-700"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>

                <div class="grid md:grid-cols-3 gap-3">
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">File *</label>
                    <input
                      v-model="section.file"
                      type="text"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                      placeholder="cover.html"
                      required
                    />
                  </div>

                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Label *</label>
                    <input
                      v-model="section.label"
                      type="text"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                      placeholder="Cover"
                      required
                    />
                  </div>

                  <div class="flex items-end space-x-2">
                    <div class="flex-1">
                      <label class="block text-xs font-medium text-gray-600 mb-1">Order</label>
                      <input
                        v-model.number="section.sort_order"
                        type="number"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                        min="1"
                        required
                      />
                    </div>
                    <label class="flex items-center pb-2">
                      <input v-model="section.is_required" type="checkbox" class="mr-1" />
                      <span class="text-xs font-medium text-gray-600">Required</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Ornaments -->
          <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-xl font-bold text-gray-900">Ornaments (Opsional)</h2>
              <button
                type="button"
                @click="addOrnament"
                class="bg-purple-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-purple-700 transition"
              >
                + Tambah Ornament
              </button>
            </div>

            <div v-if="form.ornaments.length === 0" class="text-center py-8 text-gray-500">
              Belum ada ornament. Klik tombol di atas untuk menambahkan.
            </div>

            <div class="space-y-4">
              <div
                v-for="(ornament, index) in form.ornaments"
                :key="index"
                class="border border-gray-200 rounded-lg p-4"
              >
                <div class="flex items-start justify-between mb-3">
                  <span class="text-sm font-semibold text-gray-700">Ornament #{{ index + 1 }}</span>
                  <button
                    type="button"
                    @click="removeOrnament(index)"
                    class="text-red-600 hover:text-red-700"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>

                <div class="grid md:grid-cols-3 gap-3">
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">File *</label>
                    <input
                      v-model="ornament.file"
                      type="text"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                      placeholder="batik-corner.html"
                      required
                    />
                  </div>

                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Label *</label>
                    <input
                      v-model="ornament.label"
                      type="text"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                      placeholder="Batik Corner"
                      required
                    />
                  </div>

                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Position *</label>
                    <select
                      v-model="ornament.position"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                      required
                    >
                      <option value="top">Top</option>
                      <option value="top-left">Top Left</option>
                      <option value="top-right">Top Right</option>
                      <option value="center">Center</option>
                      <option value="bottom">Bottom</option>
                      <option value="bottom-left">Bottom Left</option>
                      <option value="bottom-right">Bottom Right</option>
                      <option value="side">Side</option>
                      <option value="floating">Floating</option>
                      <option value="background">Background</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Submit -->
          <div class="flex items-center space-x-4">
            <button
              type="submit"
              :disabled="form.processing"
              class="bg-gradient-to-r from-pink-600 to-purple-600 text-white px-8 py-3 rounded-lg font-semibold hover:shadow-lg transition disabled:opacity-50"
            >
              {{ form.processing ? 'Menyimpan...' : 'Update Template' }}
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
    </div>
  </div>
</template>
