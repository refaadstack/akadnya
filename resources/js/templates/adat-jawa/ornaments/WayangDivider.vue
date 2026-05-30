<script setup lang="ts">
defineProps<{
  position?: string
}>()
</script>

<template>
  <div class="w-full py-8 flex justify-center">
    <!-- Wayang-inspired Divider -->
    <svg
      class="w-64 h-16 md:w-96 md:h-20"
      viewBox="0 0 400 80"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
    >
      <!-- Center Ornament (Wayang Crown) -->
      <g transform="translate(200, 40)">
        <!-- Crown Base -->
        <path
          d="M -30 0 L -25 -15 L -20 -10 L -15 -20 L -10 -12 L -5 -25 L 0 -30 L 5 -25 L 10 -12 L 15 -20 L 20 -10 L 25 -15 L 30 0 Z"
          fill="#92400e"
          opacity="0.6"
        />
        
        <!-- Center Diamond -->
        <circle cx="0" cy="-15" r="4" fill="#d97706" opacity="0.8"/>
        
        <!-- Side Decorations -->
        <circle cx="-15" cy="-10" r="3" fill="#d97706" opacity="0.7"/>
        <circle cx="15" cy="-10" r="3" fill="#d97706" opacity="0.7"/>
        
        <!-- Base Line -->
        <rect x="-30" y="0" width="60" height="3" fill="#92400e" opacity="0.5"/>
      </g>

      <!-- Left Flourish -->
      <g transform="translate(100, 40)">
        <path
          d="M 0 0 Q -20 -10, -40 -5 Q -50 -3, -60 0"
          stroke="#92400e"
          stroke-width="2"
          fill="none"
          opacity="0.5"
        />
        <circle cx="-20" cy="-8" r="2" fill="#d97706" opacity="0.6"/>
        <circle cx="-40" cy="-4" r="2" fill="#d97706" opacity="0.6"/>
      </g>

      <!-- Right Flourish -->
      <g transform="translate(300, 40)">
        <path
          d="M 0 0 Q 20 -10, 40 -5 Q 50 -3, 60 0"
          stroke="#92400e"
          stroke-width="2"
          fill="none"
          opacity="0.5"
        />
        <circle cx="20" cy="-8" r="2" fill="#d97706" opacity="0.6"/>
        <circle cx="40" cy="-4" r="2" fill="#d97706" opacity="0.6"/>
      </g>

      <!-- Decorative Dots -->
      <circle cx="50" cy="40" r="1.5" fill="#92400e" opacity="0.4"/>
      <circle cx="350" cy="40" r="1.5" fill="#92400e" opacity="0.4"/>
      
      <!-- Side Lines -->
      <line x1="10" y1="40" x2="40" y2="40" stroke="#92400e" stroke-width="1" opacity="0.3"/>
      <line x1="360" y1="40" x2="390" y2="40" stroke="#92400e" stroke-width="1" opacity="0.3"/>
    </svg>
  </div>
</template>
