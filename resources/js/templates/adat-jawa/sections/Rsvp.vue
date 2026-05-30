<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

interface InvitationContent {
  bride_name: string
  groom_name: string
}

const props = defineProps<{
  content: InvitationContent
  guestName?: string | null
  invitationId: number
  subdomain: string
}>()

const showSuccess = ref(false)

const form = useForm({
  name: props.guestName || '',
  attendance: 'yes',
  pax_count: 1,
  message: '',
})

const submit = () => {
  form.post(`/i/${props.subdomain}/rsvp`, {
    preserveScroll: true,
    onSuccess: () => {
      showSuccess.value = true
      form.reset('message')
      setTimeout(() => {
        showSuccess.value = false
      }, 5000)
    },
  })
}
</script>

<template>
  <section id="rsvp" class="py-20 px-4 bg-gradient-to-b from-amber-50 to-amber-100">
    <div class="max-w-2xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-12">
        <h2 class="text-4xl font-serif text-amber-900 mb-4">Konfirmasi Kehadiran</h2>
        <div class="w-24 h-1 bg-amber-600 mx-auto mb-6"></div>
        <p class="text-amber-800">
          Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i
          berkenan hadir untuk memberikan doa restu kepada kedua mempelai.
        </p>
      </div>

      <!-- Success Message -->
      <div
        v-if="showSuccess"
        class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg"
      >
        ✓ Terima kasih! Konfirmasi kehadiran Anda telah kami terima.
      </div>

      <!-- RSVP Form -->
      <form @submit.prevent="submit" class="bg-white rounded-lg shadow-lg p-8 border-2 border-amber-200">
        <!-- Name -->
        <div class="mb-6">
          <label class="block text-amber-900 font-medium mb-2">
            Nama Lengkap <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.name"
            type="text"
            required
            class="w-full px-4 py-3 border-2 border-amber-200 rounded-lg focus:border-amber-500 focus:outline-none"
            placeholder="Masukkan nama Anda"
          />
        </div>

        <!-- Attendance -->
        <div class="mb-6">
          <label class="block text-amber-900 font-medium mb-2">
            Konfirmasi Kehadiran <span class="text-red-500">*</span>
          </label>
          <div class="grid grid-cols-2 gap-4">
            <label
              class="flex items-center justify-center p-4 border-2 rounded-lg cursor-pointer transition"
              :class="form.attendance === 'yes' ? 'border-amber-600 bg-amber-50' : 'border-amber-200 hover:border-amber-400'"
            >
              <input
                v-model="form.attendance"
                type="radio"
                value="yes"
                class="mr-3"
              />
              <span class="text-amber-900 font-medium">Hadir</span>
            </label>
            <label
              class="flex items-center justify-center p-4 border-2 rounded-lg cursor-pointer transition"
              :class="form.attendance === 'no' ? 'border-amber-600 bg-amber-50' : 'border-amber-200 hover:border-amber-400'"
            >
              <input
                v-model="form.attendance"
                type="radio"
                value="no"
                class="mr-3"
              />
              <span class="text-amber-900 font-medium">Tidak Hadir</span>
            </label>
          </div>
        </div>

        <!-- Pax Count (only if attending) -->
        <div v-if="form.attendance === 'yes'" class="mb-6">
          <label class="block text-amber-900 font-medium mb-2">
            Jumlah Tamu <span class="text-red-500">*</span>
          </label>
          <input
            v-model.number="form.pax_count"
            type="number"
            min="1"
            max="10"
            required
            class="w-full px-4 py-3 border-2 border-amber-200 rounded-lg focus:border-amber-500 focus:outline-none"
          />
          <p class="text-sm text-amber-600 mt-1">Termasuk Anda sendiri</p>
        </div>

        <!-- Message -->
        <div class="mb-6">
          <label class="block text-amber-900 font-medium mb-2">
            Ucapan & Doa
          </label>
          <textarea
            v-model="form.message"
            rows="4"
            class="w-full px-4 py-3 border-2 border-amber-200 rounded-lg focus:border-amber-500 focus:outline-none resize-none"
            placeholder="Berikan ucapan dan doa untuk kedua mempelai..."
          ></textarea>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="form.processing"
          class="w-full bg-amber-700 hover:bg-amber-800 text-white font-medium py-4 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="form.processing">Mengirim...</span>
          <span v-else>Kirim Konfirmasi</span>
        </button>
      </form>
    </div>
  </section>
</template>
