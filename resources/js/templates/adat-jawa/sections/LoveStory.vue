<script setup lang="ts">
defineProps<{
  content: any
}>()
</script>

<template>
  <section v-if="content.love_story" class="py-20 px-4 bg-white">
    <div class="container mx-auto max-w-4xl">
      <!-- Title -->
      <div class="text-center mb-16">
        <h2 class="text-4xl md:text-5xl font-bold text-amber-800 mb-4 font-serif">Cerita Cinta Kami</h2>
        <div class="flex items-center justify-center">
          <div class="h-px w-20 bg-amber-400"></div>
          <svg class="w-8 h-8 mx-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
          </svg>
          <div class="h-px w-20 bg-amber-400"></div>
        </div>
      </div>

      <!-- Story Content -->
      <div class="bg-gradient-to-br from-amber-50 to-white rounded-2xl shadow-xl p-8 md:p-12 border-4 border-amber-200">
        <!-- Decorative Quote -->
        <div class="text-amber-600 mb-6">
          <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24">
            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
          </svg>
        </div>

        <!-- Story Text -->
        <div class="prose prose-lg max-w-none">
          <p class="text-gray-700 leading-relaxed whitespace-pre-line text-lg">
            {{ content.love_story }}
          </p>
        </div>

        <!-- Decorative Quote End -->
        <div class="text-amber-600 mt-6 flex justify-end">
          <svg class="w-12 h-12 transform rotate-180" fill="currentColor" viewBox="0 0 24 24">
            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
          </svg>
        </div>

        <!-- Hearts Decoration -->
        <div class="flex justify-center mt-8 space-x-2">
          <svg class="w-6 h-6 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
          </svg>
          <svg class="w-6 h-6 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
          </svg>
          <svg class="w-6 h-6 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
          </svg>
        </div>
      </div>
    </div>
  </section>
</template>
