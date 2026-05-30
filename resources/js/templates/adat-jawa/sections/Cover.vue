<script setup lang="ts">
import { ref } from 'vue'

const props = defineProps<{
  content: any
  guestName?: string | null
}>()

const showContent = ref(false)

const openInvitation = () => {
  showContent.value = true
  // Smooth scroll to next section
  setTimeout(() => {
    window.scrollTo({ top: window.innerHeight, behavior: 'smooth' })
  }, 300)
}
</script>

<template>
  <section class="relative h-screen flex items-center justify-center overflow-hidden">
    <!-- Background Image -->
    <div
      class="absolute inset-0 bg-cover bg-center"
      :style="{ backgroundImage: content.cover_photo_url ? `url(${content.cover_photo_url})` : 'none' }"
    >
      <div class="absolute inset-0 bg-gradient-to-b from-amber-900/60 via-amber-800/50 to-amber-900/70"></div>
    </div>

    <!-- Batik Pattern Overlay -->
    <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23000000\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')"></div>

    <!-- Content -->
    <div class="relative z-10 text-center px-4 max-w-4xl">
      <!-- Guest Name -->
      <div v-if="guestName" class="mb-8 animate-fade-in">
        <p class="text-amber-200 text-lg mb-2 font-serif">Kepada Yth.</p>
        <p class="text-white text-3xl font-bold font-serif">{{ guestName }}</p>
      </div>

      <!-- Ornamental Divider -->
      <div class="flex items-center justify-center mb-6">
        <div class="h-px w-16 bg-amber-300"></div>
        <svg class="w-8 h-8 mx-4 text-amber-300" fill="currentColor" viewBox="0 0 20 20">
          <path d="M10 3.5a1.5 1.5 0 013 0V4a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-.5a1.5 1.5 0 000 3h.5a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-.5a1.5 1.5 0 00-3 0v.5a1 1 0 01-1 1H6a1 1 0 01-1-1v-3a1 1 0 00-1-1h-.5a1.5 1.5 0 010-3H4a1 1 0 001-1V6a1 1 0 011-1h3a1 1 0 001-1v-.5z" />
        </svg>
        <div class="h-px w-16 bg-amber-300"></div>
      </div>

      <!-- Title -->
      <h1 class="text-amber-200 text-xl md:text-2xl mb-4 font-serif tracking-widest">
        THE WEDDING OF
      </h1>

      <!-- Names -->
      <div class="mb-8">
        <h2 class="text-white text-5xl md:text-7xl font-bold mb-2 font-serif">
          {{ content.bride_name }}
        </h2>
        <p class="text-amber-300 text-4xl md:text-5xl font-serif">&</p>
        <h2 class="text-white text-5xl md:text-7xl font-bold mt-2 font-serif">
          {{ content.groom_name }}
        </h2>
      </div>

      <!-- Date -->
      <p v-if="content.akad_datetime" class="text-amber-200 text-xl md:text-2xl mb-8 font-serif">
        {{ new Date(content.akad_datetime).toLocaleDateString('id-ID', { 
          weekday: 'long', 
          year: 'numeric', 
          month: 'long', 
          day: 'numeric' 
        }) }}
      </p>

      <!-- Open Button -->
      <button
        @click="openInvitation"
        class="bg-amber-600 hover:bg-amber-700 text-white px-10 py-4 rounded-full text-lg font-semibold shadow-xl hover:shadow-2xl transform hover:scale-105 transition-all duration-300 font-serif"
      >
        <svg class="w-6 h-6 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76" />
        </svg>
        Buka Undangan
      </button>
    </div>

    <!-- Scroll Indicator -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
      <svg class="w-6 h-6 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
      </svg>
    </div>
  </section>
</template>

<style scoped>
@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fade-in 1s ease-out;
}
</style>
