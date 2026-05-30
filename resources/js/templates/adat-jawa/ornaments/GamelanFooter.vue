<script setup lang="ts">
defineProps<{
  position?: string
}>()
</script>

<template>
  <div class="w-full bg-gradient-to-t from-amber-900 to-amber-800 py-6">
    <div class="max-w-6xl mx-auto px-4">
      <!-- Gamelan Instruments Pattern -->
      <div class="flex justify-center items-end space-x-4 md:space-x-8 opacity-30">
        <!-- Gong -->
        <svg class="w-12 h-12 md:w-16 md:h-16" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="32" cy="40" r="20" stroke="#fbbf24" stroke-width="2" fill="none"/>
          <circle cx="32" cy="40" r="15" stroke="#fbbf24" stroke-width="1.5" fill="none"/>
          <circle cx="32" cy="40" r="5" fill="#fbbf24"/>
          <line x1="32" y1="10" x2="32" y2="20" stroke="#fbbf24" stroke-width="2"/>
          <circle cx="32" cy="10" r="3" fill="#fbbf24"/>
        </svg>

        <!-- Kenong -->
        <svg class="w-10 h-10 md:w-14 md:h-14" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
          <ellipse cx="32" cy="45" rx="18" ry="12" stroke="#fbbf24" stroke-width="2" fill="none"/>
          <ellipse cx="32" cy="45" rx="12" ry="8" stroke="#fbbf24" stroke-width="1.5" fill="none"/>
          <circle cx="32" cy="45" r="4" fill="#fbbf24"/>
          <rect x="28" y="25" width="8" height="15" fill="#fbbf24" opacity="0.6"/>
        </svg>

        <!-- Bonang -->
        <svg class="w-14 h-14 md:w-18 md:h-18" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="24" cy="45" r="10" stroke="#fbbf24" stroke-width="2" fill="none"/>
          <circle cx="24" cy="45" r="4" fill="#fbbf24"/>
          <circle cx="40" cy="45" r="10" stroke="#fbbf24" stroke-width="2" fill="none"/>
          <circle cx="40" cy="45" r="4" fill="#fbbf24"/>
          <line x1="20" y1="55" x2="44" y2="55" stroke="#fbbf24" stroke-width="2"/>
        </svg>

        <!-- Saron -->
        <svg class="w-16 h-16 md:w-20 md:h-20" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect x="12" y="35" width="6" height="20" fill="#fbbf24" opacity="0.7"/>
          <rect x="20" y="30" width="6" height="25" fill="#fbbf24" opacity="0.7"/>
          <rect x="28" y="32" width="6" height="23" fill="#fbbf24" opacity="0.7"/>
          <rect x="36" y="28" width="6" height="27" fill="#fbbf24" opacity="0.7"/>
          <rect x="44" y="33" width="6" height="22" fill="#fbbf24" opacity="0.7"/>
          <line x1="10" y1="56" x2="52" y2="56" stroke="#fbbf24" stroke-width="3"/>
        </svg>

        <!-- Kenong (repeat) -->
        <svg class="w-10 h-10 md:w-14 md:h-14" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
          <ellipse cx="32" cy="45" rx="18" ry="12" stroke="#fbbf24" stroke-width="2" fill="none"/>
          <ellipse cx="32" cy="45" rx="12" ry="8" stroke="#fbbf24" stroke-width="1.5" fill="none"/>
          <circle cx="32" cy="45" r="4" fill="#fbbf24"/>
          <rect x="28" y="25" width="8" height="15" fill="#fbbf24" opacity="0.6"/>
        </svg>

        <!-- Gong (smaller) -->
        <svg class="w-12 h-12 md:w-16 md:h-16" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="32" cy="40" r="20" stroke="#fbbf24" stroke-width="2" fill="none"/>
          <circle cx="32" cy="40" r="15" stroke="#fbbf24" stroke-width="1.5" fill="none"/>
          <circle cx="32" cy="40" r="5" fill="#fbbf24"/>
          <line x1="32" y1="10" x2="32" y2="20" stroke="#fbbf24" stroke-width="2"/>
          <circle cx="32" cy="10" r="3" fill="#fbbf24"/>
        </svg>
      </div>

      <!-- Decorative Wave Pattern -->
      <div class="mt-4">
        <svg class="w-full h-8" viewBox="0 0 1200 40" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
          <path
            d="M 0 20 Q 50 10, 100 20 T 200 20 T 300 20 T 400 20 T 500 20 T 600 20 T 700 20 T 800 20 T 900 20 T 1000 20 T 1100 20 T 1200 20"
            stroke="#fbbf24"
            stroke-width="1"
            fill="none"
            opacity="0.3"
          />
          <path
            d="M 0 30 Q 50 25, 100 30 T 200 30 T 300 30 T 400 30 T 500 30 T 600 30 T 700 30 T 800 30 T 900 30 T 1000 30 T 1100 30 T 1200 30"
            stroke="#fbbf24"
            stroke-width="1"
            fill="none"
            opacity="0.2"
          />
        </svg>
      </div>
    </div>
  </div>
</template>
