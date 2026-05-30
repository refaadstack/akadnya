<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
</script>

<template>
  <footer class="bg-gray-900 text-white py-12">
    <div class="container mx-auto px-4">
      <div class="grid md:grid-cols-4 gap-8 mb-8">
        <!-- Brand -->
        <div>
          <div class="flex items-center space-x-2 mb-4">
            <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-purple-600 rounded-lg flex items-center justify-center">
              <span class="text-white font-bold text-xl">M</span>
            </div>
            <span class="text-xl font-bold">MyAkad</span>
          </div>
          <p class="text-gray-400 text-sm">
            Platform undangan digital pernikahan online terpercaya di Indonesia.
          </p>
        </div>

        <!-- Produk -->
        <div>
          <h4 class="font-semibold mb-4">Produk</h4>
          <ul class="space-y-2 text-sm text-gray-400">
            <li>
              <Link href="/templates" class="hover:text-white transition">
                Template
              </Link>
            </li>
            <li>
              <Link href="/#features" class="hover:text-white transition">
                Fitur
              </Link>
            </li>
            <li>
              <Link href="/#pricing" class="hover:text-white transition">
                Harga
              </Link>
            </li>
          </ul>
        </div>

        <!-- Bantuan -->
        <div>
          <h4 class="font-semibold mb-4">Bantuan</h4>
          <ul class="space-y-2 text-sm text-gray-400">
            <li>
              <Link href="/faq" class="hover:text-white transition">
                FAQ
              </Link>
            </li>
            <li>
              <a href="mailto:support@myakad.com" class="hover:text-white transition">
                Support
              </a>
            </li>
            <li>
              <a href="https://wa.me/6281234567890" target="_blank" class="hover:text-white transition">
                WhatsApp
              </a>
            </li>
          </ul>
        </div>

        <!-- Perusahaan -->
        <div>
          <h4 class="font-semibold mb-4">Perusahaan</h4>
          <ul class="space-y-2 text-sm text-gray-400">
            <li>
              <Link href="/about" class="hover:text-white transition">
                Tentang Kami
              </Link>
            </li>
            <li>
              <Link href="/privacy" class="hover:text-white transition">
                Kebijakan Privasi
              </Link>
            </li>
            <li>
              <Link href="/terms" class="hover:text-white transition">
                Syarat & Ketentuan
              </Link>
            </li>
          </ul>
        </div>
      </div>

      <!-- Copyright -->
      <div class="border-t border-gray-800 pt-8 text-center text-sm text-gray-400">
        <p>&copy; {{ new Date().getFullYear() }} MyAkad. All rights reserved.</p>
      </div>
    </div>
  </footer>
</template>
