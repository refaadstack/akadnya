<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'

defineOptions({
  layout: undefined
})

interface Section {
  id: number
  section_key: string
  name: string
  is_visible: boolean
  sort_order: number
}

interface Ornament {
  id: number
  ornament_key: string
  name: string
  position: string
  is_active: boolean
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
  sections: Section[]
  ornaments: Ornament[]
}>()

const sections = ref([...props.sections])
const ornaments = ref([...props.ornaments])
const draggedSection = ref<number | null>(null)

// Drag and drop handlers
const handleDragStart = (index: number) => {
  draggedSection.value = index
}

const handleDragOver = (event: DragEvent) => {
  event.preventDefault()
}

const handleDrop = (index: number) => {
  if (draggedSection.value === null) {
    return
  }
  
  const items = [...sections.value]
  const draggedItem = items[draggedSection.value]
  items.splice(draggedSection.value, 1)
  items.splice(index, 0, draggedItem)
  
  sections.value = items
  draggedSection.value = null
  
  // Save new order
  saveOrder()
}

const saveOrder = async () => {
  const sectionIds = sections.value.map(s => s.id)
  const xsrfToken = decodeURIComponent(
    document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] ?? ''
  )

  try {
    await fetch('/dashboard/sections/reorder', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-XSRF-TOKEN': xsrfToken,
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({ section_ids: sectionIds }),
    })
  } catch (error) {
    console.error('Failed to save order:', error)
    alert('Gagal menyimpan urutan section')
  }
}

const toggleSection = async (sectionId: number) => {
  const xsrfToken = decodeURIComponent(
    document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] ?? ''
  )

  try {
    const response = await fetch(`/dashboard/sections/${sectionId}/toggle`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-XSRF-TOKEN': xsrfToken,
        'X-Requested-With': 'XMLHttpRequest',
      },
    })
    
    const data = await response.json()
    
    if (data.success) {
      const section = sections.value.find(s => s.id === sectionId)

      if (section) {
        section.is_visible = data.is_visible
      }
    } else {
      alert(data.message || 'Gagal toggle section')
    }
  } catch (error) {
    console.error('Failed to toggle section:', error)
    alert('Gagal toggle section')
  }
}

const toggleOrnament = async (ornamentId: number) => {
  const xsrfToken = decodeURIComponent(
    document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] ?? ''
  )

  try {
    const response = await fetch(`/dashboard/ornaments/${ornamentId}/toggle`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-XSRF-TOKEN': xsrfToken,
        'X-Requested-With': 'XMLHttpRequest',
      },
    })
    
    const data = await response.json()
    
    if (data.success) {
      const ornament = ornaments.value.find(o => o.id === ornamentId)

      if (ornament) {
        ornament.is_active = data.is_active
      }
    }
  } catch (error) {
    console.error('Failed to toggle ornament:', error)
    alert('Gagal toggle ornament')
  }
}

const getPositionLabel = (position: string) => {
  const labels: Record<string, string> = {
    top: 'Atas',
    bottom: 'Bawah',
    between: 'Antar Section',
    overlay: 'Overlay',
  }

  return labels[position] || position
}
</script>

<template>
  <DashboardLayout>
    <Head title="Kustomisasi Undangan" />

    <div class="min-h-screen bg-gray-50">
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
            <h1 class="text-3xl font-bold text-gray-900">Kustomisasi Undangan</h1>
            <p class="text-gray-600 mt-1">Template: {{ invitation.template.name }}</p>
          </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
          <!-- Sections Management -->
          <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-xl font-bold text-gray-900">Kelola Section</h2>
              <div class="text-sm text-gray-600">
                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                </svg>
                Drag untuk reorder
              </div>
            </div>

            <div class="space-y-3">
              <div
                v-for="(section, index) in sections"
                :key="section.id"
                draggable="true"
                @dragstart="handleDragStart(index)"
                @dragover="handleDragOver"
                @drop="handleDrop(index)"
                class="flex items-center justify-between p-4 border-2 border-gray-200 rounded-lg cursor-move hover:border-pink-300 transition"
                :class="{ 'opacity-50': !section.is_visible }"
              >
                <div class="flex items-center space-x-3">
                  <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                  </svg>
                  <div>
                    <p class="font-semibold text-gray-900">{{ section.name }}</p>
                    <p class="text-xs text-gray-500">{{ section.section_key }}</p>
                  </div>
                </div>

                <label class="relative inline-flex items-center cursor-pointer">
                  <input
                    type="checkbox"
                    :checked="section.is_visible"
                    @change="toggleSection(section.id)"
                    class="sr-only peer"
                  />
                  <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-pink-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-600"></div>
                </label>
              </div>
            </div>

            <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
              <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <div class="text-sm text-blue-800">
                  <p class="font-semibold">Tips:</p>
                  <p class="mt-1">Section yang wajib (hero, countdown, rsvp) tidak dapat disembunyikan.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Ornaments Management -->
          <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Kelola Ornamen</h2>

            <div class="space-y-6">
              <div v-for="position in ['top', 'bottom', 'between', 'overlay']" :key="position">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">{{ getPositionLabel(position) }}</h3>
                
                <div class="space-y-2">
                  <div
                    v-for="ornament in ornaments.filter(o => o.position === position)"
                    :key="ornament.id"
                    class="flex items-center justify-between p-3 border border-gray-200 rounded-lg"
                  >
                    <div>
                      <p class="font-medium text-gray-900">{{ ornament.name }}</p>
                      <p class="text-xs text-gray-500">{{ ornament.ornament_key }}</p>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer">
                      <input
                        type="checkbox"
                        :checked="ornament.is_active"
                        @change="toggleOrnament(ornament.id)"
                        class="sr-only peer"
                      />
                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-pink-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-600"></div>
                    </label>
                  </div>

                  <p v-if="ornaments.filter(o => o.position === position).length === 0" class="text-sm text-gray-500 italic">
                    Tidak ada ornamen di posisi ini
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Preview Button -->
        <div class="mt-8 text-center">
          <a
            :href="`/templates/${invitation.template.slug}/render`"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center bg-gradient-to-r from-pink-600 to-purple-600 text-white px-8 py-3 rounded-lg font-semibold hover:shadow-lg transition"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            Preview Undangan
          </a>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>
