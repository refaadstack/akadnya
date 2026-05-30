<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps<{
  canRegister?: boolean
  currentPage?: string
}>()

const mobileMenuOpen = ref(false)
</script>

<template>
  <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-200">
    <div class="container mx-auto px-4 py-4">
      <div class="flex items-center justify-between">
        <!-- Logo -->
        <Link href="/" class="flex items-center space-x-2">
          <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-purple-600 rounded-lg flex items-center justify-center">
            <span class="text-white font-bold text-xl">M</span>
          </div>
          <span class="text-2xl font-bold bg-gradient-to-r from-pink-600 to-purple-600 bg-clip-text text-transparent">
            MyAkad
          </span>
        </Link>

        <!-- Desktop Menu -->
        <div class="hidden md:flex items-center space-x-6">
          <Link
            href="/"
            class="font-medium transition"
            :class="currentPage === 'home' ? 'text-pink-600' : 'text-gray-700 hover:text-pink-600'"
          >
            Beranda
          </Link>
          <Link
            href="/templates"
            class="font-medium transition"
            :class="currentPage === 'templates' ? 'text-pink-600' : 'text-gray-700 hover:text-pink-600'"
          >
            Template
          </Link>
          <Link
            href="/faq"
            class="font-medium transition"
            :class="currentPage === 'faq' ? 'text-pink-600' : 'text-gray-700 hover:text-pink-600'"
          >
            FAQ
          </Link>
          <Link
            href="/login"
            class="text-gray-700 hover:text-pink-600 font-medium transition"
          >
            Masuk
          </Link>
          <Link
            v-if="canRegister"
            href="/register"
            class="bg-gradient-to-r from-pink-600 to-purple-600 text-white px-6 py-2 rounded-full font-semibold hover:shadow-lg transition"
          >
            Daftar Gratis
          </Link>
        </div>

        <!-- Mobile Menu Button -->
        <button
          @click="mobileMenuOpen = !mobileMenuOpen"
          class="md:hidden text-gray-700 hover:text-pink-600 transition"
        >
          <svg v-if="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
          <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Mobile Menu -->
      <div v-if="mobileMenuOpen" class="md:hidden mt-4 pb-4 border-t border-gray-200 pt-4">
        <div class="flex flex-col space-y-3">
          <Link
            href="/"
            class="font-medium transition py-2"
            :class="currentPage === 'home' ? 'text-pink-600' : 'text-gray-700 hover:text-pink-600'"
            @click="mobileMenuOpen = false"
          >
            Beranda
          </Link>
          <Link
            href="/templates"
            class="font-medium transition py-2"
            :class="currentPage === 'templates' ? 'text-pink-600' : 'text-gray-700 hover:text-pink-600'"
            @click="mobileMenuOpen = false"
          >
            Template
          </Link>
          <Link
            href="/faq"
            class="font-medium transition py-2"
            :class="currentPage === 'faq' ? 'text-pink-600' : 'text-gray-700 hover:text-pink-600'"
            @click="mobileMenuOpen = false"
          >
            FAQ
          </Link>
          <Link
            href="/login"
            class="text-gray-700 hover:text-pink-600 font-medium transition py-2"
            @click="mobileMenuOpen = false"
          >
            Masuk
          </Link>
          <Link
            v-if="canRegister"
            href="/register"
            class="bg-gradient-to-r from-pink-600 to-purple-600 text-white px-6 py-3 rounded-full font-semibold hover:shadow-lg transition text-center"
            @click="mobileMenuOpen = false"
          >
            Daftar Gratis
          </Link>
        </div>
      </div>
    </div>
  </nav>
</template>
