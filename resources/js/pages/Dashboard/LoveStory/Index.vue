<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps<{
  love_story: string
  special_message: string
}>()

const form = useForm({
  love_story: props.love_story,
  special_message: props.special_message,
})

const submit = () => {
  form.post(route('dashboard.love-story.update'), {
    preserveScroll: true,
  })
}
</script>

<template>
  <DashboardLayout>
    <Head title="Love Story" />

    <div class="container mx-auto px-4 py-8">
      <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900">Love Story</h1>
          <p class="text-gray-600 mt-2">Ceritakan kisah cinta kalian dan pesan khusus untuk tamu undangan</p>
        </div>

        <!-- Flash Messages -->
        <div v-if="$page.props.flash?.success" class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
          {{ $page.props.flash.success }}
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Love Story -->
          <div class="bg-white rounded-xl shadow-sm p-6">
            <label class="block text-lg font-semibold text-gray-900 mb-2">
              Kisah Cinta Kami
            </label>
            <p class="text-sm text-gray-600 mb-4">
              Ceritakan bagaimana kalian bertemu dan jatuh cinta. Kisah ini akan ditampilkan di halaman undangan.
            </p>
            <textarea
              v-model="form.love_story"
              rows="10"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
              placeholder="Tuliskan kisah cinta kalian di sini..."
            />
            <p v-if="form.errors.love_story" class="text-red-600 text-sm mt-2">
              {{ form.errors.love_story }}
            </p>
          </div>

          <!-- Special Message -->
          <div class="bg-white rounded-xl shadow-sm p-6">
            <label class="block text-lg font-semibold text-gray-900 mb-2">
              Pesan Khusus untuk Tamu
            </label>
            <p class="text-sm text-gray-600 mb-4">
              Sampaikan pesan khusus dan ucapan terima kasih untuk tamu yang akan hadir.
            </p>
            <textarea
              v-model="form.special_message"
              rows="6"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
              placeholder="Tuliskan pesan khusus untuk tamu undangan..."
            />
            <p v-if="form.errors.special_message" class="text-red-600 text-sm mt-2">
              {{ form.errors.special_message }}
            </p>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end">
            <button
              type="submit"
              :disabled="form.processing"
              class="bg-pink-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-pink-700 transition disabled:opacity-50"
            >
              {{ form.processing ? 'Menyimpan...' : 'Simpan Love Story' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </DashboardLayout>
</template>
