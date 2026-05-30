<script setup lang="ts">
defineProps<{
  content: any
}>()
</script>

<template>
  <section class="py-20 px-4 bg-white">
    <div class="container mx-auto max-w-6xl">
      <!-- Title -->
      <div class="text-center mb-16">
        <h2 class="text-4xl md:text-5xl font-bold text-amber-800 mb-4 font-serif">Mempelai</h2>
        <div class="flex items-center justify-center">
          <div class="h-px w-20 bg-amber-400"></div>
          <svg class="w-8 h-8 mx-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
            <path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" />
          </svg>
          <div class="h-px w-20 bg-amber-400"></div>
        </div>
      </div>

      <!-- Mempelai Grid -->
      <div class="grid md:grid-cols-2 gap-12 md:gap-16">
        <!-- Bride -->
        <div class="text-center">
          <!-- Photo Placeholder with Batik Border -->
          <div class="relative inline-block mb-6">
            <div class="w-64 h-64 mx-auto rounded-full bg-gradient-to-br from-amber-200 to-amber-400 p-2">
              <div class="w-full h-full rounded-full bg-white flex items-center justify-center overflow-hidden">
                <svg class="w-32 h-32 text-amber-300" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
            <!-- Decorative Corner -->
            <div class="absolute -top-4 -right-4 w-16 h-16 text-amber-600">
              <svg viewBox="0 0 100 100" fill="currentColor">
                <path d="M0,0 L100,0 L100,100 Q50,50 0,100 Z" />
              </svg>
            </div>
          </div>

          <!-- Name -->
          <h3 class="text-3xl md:text-4xl font-bold text-amber-900 mb-3 font-serif">
            {{ content.bride_name }}
          </h3>

          <!-- Parents -->
          <div v-if="content.bride_father || content.bride_mother" class="text-gray-600 mb-4">
            <p class="text-sm mb-2">Putri dari:</p>
            <p v-if="content.bride_father" class="font-semibold">{{ content.bride_father }}</p>
            <p v-if="content.bride_mother" class="font-semibold">{{ content.bride_mother }}</p>
          </div>

          <!-- Instagram (Optional) -->
          <a href="#" class="inline-flex items-center text-amber-700 hover:text-amber-900 transition">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073z"/>
              <path d="M12 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
            </svg>
            @bride
          </a>
        </div>

        <!-- Groom -->
        <div class="text-center">
          <!-- Photo Placeholder with Batik Border -->
          <div class="relative inline-block mb-6">
            <div class="w-64 h-64 mx-auto rounded-full bg-gradient-to-br from-amber-200 to-amber-400 p-2">
              <div class="w-full h-full rounded-full bg-white flex items-center justify-center overflow-hidden">
                <svg class="w-32 h-32 text-amber-300" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
            <!-- Decorative Corner -->
            <div class="absolute -top-4 -left-4 w-16 h-16 text-amber-600 transform scale-x-[-1]">
              <svg viewBox="0 0 100 100" fill="currentColor">
                <path d="M0,0 L100,0 L100,100 Q50,50 0,100 Z" />
              </svg>
            </div>
          </div>

          <!-- Name -->
          <h3 class="text-3xl md:text-4xl font-bold text-amber-900 mb-3 font-serif">
            {{ content.groom_name }}
          </h3>

          <!-- Parents -->
          <div v-if="content.groom_father || content.groom_mother" class="text-gray-600 mb-4">
            <p class="text-sm mb-2">Putra dari:</p>
            <p v-if="content.groom_father" class="font-semibold">{{ content.groom_father }}</p>
            <p v-if="content.groom_mother" class="font-semibold">{{ content.groom_mother }}</p>
          </div>

          <!-- Instagram (Optional) -->
          <a href="#" class="inline-flex items-center text-amber-700 hover:text-amber-900 transition">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073z"/>
              <path d="M12 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
            </svg>
            @groom
          </a>
        </div>
      </div>
    </div>
  </section>
</template>
