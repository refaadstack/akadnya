<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useHttp } from '@inertiajs/vue3'

interface Wish {
  id: number
  name: string
  message: string
  attendance: string
  created_at: string
}

const props = defineProps<{
  invitationId: number
}>()

const wishes = ref<Wish[]>([])
const loading = ref(true)
const http = useHttp()

const loadWishes = async () => {
  try {
    const response = await http.get(`/api/invitations/${props.invitationId}/wishes`)
    wishes.value = response.data.data
  } catch (error) {
    console.error('Failed to load wishes:', error)
  } finally {
    loading.value = false
  }
}

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}

onMounted(() => {
  loadWishes()
})
</script>

<template>
  <section id="wishes" class="py-20 px-4 bg-amber-50">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-12">
        <h2 class="text-4xl font-serif text-amber-900 mb-4">Ucapan & Doa</h2>
        <div class="w-24 h-1 bg-amber-600 mx-auto mb-6"></div>
        <p class="text-amber-800">
          Doa dan ucapan dari Anda sangat berarti bagi kami
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-amber-200 border-t-amber-600"></div>
        <p class="text-amber-700 mt-4">Memuat ucapan...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="wishes.length === 0" class="text-center py-12">
        <svg class="w-24 h-24 mx-auto text-amber-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
        <p class="text-amber-700 text-lg">Belum ada ucapan</p>
        <p class="text-amber-600 text-sm mt-2">Jadilah yang pertama memberikan ucapan dan doa</p>
      </div>

      <!-- Wishes List -->
      <div v-else class="space-y-6">
        <div
          v-for="wish in wishes"
          :key="wish.id"
          class="bg-white rounded-lg shadow-md p-6 border-l-4 border-amber-500 hover:shadow-lg transition"
        >
          <!-- Header -->
          <div class="flex items-start justify-between mb-3">
            <div class="flex items-center">
              <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center mr-4">
                <span class="text-amber-800 font-bold text-lg">
                  {{ wish.name.charAt(0).toUpperCase() }}
                </span>
              </div>
              <div>
                <h3 class="font-semibold text-amber-900">{{ wish.name }}</h3>
                <p class="text-sm text-amber-600">{{ formatDate(wish.created_at) }}</p>
              </div>
            </div>
            <span
              v-if="wish.attendance === 'yes'"
              class="px-3 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full"
            >
              ✓ Hadir
            </span>
            <span
              v-else-if="wish.attendance === 'no'"
              class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded-full"
            >
              Tidak Hadir
            </span>
          </div>

          <!-- Message -->
          <p v-if="wish.message" class="text-amber-800 leading-relaxed pl-16">
            "{{ wish.message }}"
          </p>
          <p v-else class="text-amber-500 italic pl-16">
            Tidak ada pesan
          </p>
        </div>
      </div>

      <!-- Show More Button (if needed in future) -->
      <div v-if="wishes.length >= 10" class="text-center mt-8">
        <button
          class="px-6 py-3 bg-amber-700 hover:bg-amber-800 text-white rounded-lg transition"
        >
          Muat Lebih Banyak
        </button>
      </div>
    </div>
  </section>
</template>
