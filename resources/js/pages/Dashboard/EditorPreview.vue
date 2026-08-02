<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'

defineOptions({ layout: undefined })

const props = defineProps<{
  invitation?: any
  error?: string
}>()
</script>

<template>
  <div>
    <Head title="Preview Undangan" />

    <div class="min-h-screen bg-gray-900">
      <!-- Top Bar -->
      <div class="bg-gray-800 border-b border-gray-700">
        <div class="container mx-auto px-4 py-3 flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <Link
              href="/dashboard/editor"
              class="text-gray-300 hover:text-white transition"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </Link>
            <div>
              <h1 class="text-white font-semibold">Preview Mode</h1>
              <p class="text-xs text-gray-400">Template: {{ invitation?.template?.name || 'N/A' }}</p>
            </div>
          </div>

          <div class="flex items-center space-x-3">
            <span
              v-if="invitation?.status === 'published'"
              class="bg-green-500 text-white text-xs font-semibold px-3 py-1 rounded-full"
            >
              Published
            </span>
            <span
              v-else
              class="bg-gray-600 text-white text-xs font-semibold px-3 py-1 rounded-full"
            >
              Draft
            </span>

            <Link
              href="/dashboard/editor"
              class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-green-700 transition"
            >
              Kembali ke Editor
            </Link>
          </div>
        </div>
      </div>

      <!-- Error State -->
      <div v-if="error" class="container mx-auto px-4 py-16 text-center">
        <svg class="w-24 h-24 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <h2 class="text-2xl font-bold text-white mb-2">Konten Belum Tersedia</h2>
        <p class="text-gray-400 mb-6">{{ error }}</p>
        <Link
          href="/dashboard/editor"
          class="inline-block bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition"
        >
          Isi Konten Sekarang
        </Link>
      </div>

      <!-- Preview Content -->
      <div v-else class="h-[calc(100vh-64px)]">
        <!-- Placeholder for actual template rendering -->
        <div class="h-full bg-gradient-to-br from-green-50 via-white to-emerald-50 overflow-auto">
          <div class="container mx-auto px-4 py-16">
            <!-- Cover Section -->
            <div class="relative h-screen flex items-center justify-center mb-16">
              <div
                v-if="invitation.content.cover_photo_url"
                class="absolute inset-0 bg-cover bg-center"
                :style="{ backgroundImage: `url(${invitation.content.cover_photo_url})` }"
              >
                <div class="absolute inset-0 bg-black bg-opacity-40"></div>
              </div>
              <div
                v-else
                class="absolute inset-0 bg-gradient-to-br from-green-500 to-emerald-700"
              ></div>

              <div class="relative z-10 text-center text-white px-4">
                <p class="text-lg mb-2">The Wedding of</p>
                <h1 class="text-5xl md:text-7xl font-bold mb-4">
                  {{ invitation.content.bride_name }} & {{ invitation.content.groom_name }}
                </h1>
                <p v-if="invitation.content.akad_datetime" class="text-xl md:text-2xl">
                  {{ new Date(invitation.content.akad_datetime).toLocaleDateString('id-ID', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                  }) }}
                </p>
              </div>
            </div>

            <!-- Mempelai Section -->
            <div class="max-w-4xl mx-auto mb-16">
              <h2 class="text-3xl font-bold text-center text-gray-900 mb-12">Mempelai</h2>
              <div class="grid md:grid-cols-2 gap-8">
                <!-- Bride -->
                <div class="text-center">
                  <div class="w-48 h-48 mx-auto mb-4 rounded-full bg-gradient-to-br from-green-200 to-green-200"></div>
                  <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ invitation.content.bride_name }}</h3>
                  <p v-if="invitation.content.bride_father || invitation.content.bride_mother" class="text-gray-600">
                    Putri dari<br />
                    <span v-if="invitation.content.bride_father">{{ invitation.content.bride_father }}</span>
                    <span v-if="invitation.content.bride_father && invitation.content.bride_mother"> & </span>
                    <span v-if="invitation.content.bride_mother">{{ invitation.content.bride_mother }}</span>
                  </p>
                </div>

                <!-- Groom -->
                <div class="text-center">
                  <div class="w-48 h-48 mx-auto mb-4 rounded-full bg-gradient-to-br from-blue-200 to-green-200"></div>
                  <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ invitation.content.groom_name }}</h3>
                  <p v-if="invitation.content.groom_father || invitation.content.groom_mother" class="text-gray-600">
                    Putra dari<br />
                    <span v-if="invitation.content.groom_father">{{ invitation.content.groom_father }}</span>
                    <span v-if="invitation.content.groom_father && invitation.content.groom_mother"> & </span>
                    <span v-if="invitation.content.groom_mother">{{ invitation.content.groom_mother }}</span>
                  </p>
                </div>
              </div>
            </div>

            <!-- Event Details -->
            <div class="max-w-4xl mx-auto mb-16">
              <h2 class="text-3xl font-bold text-center text-gray-900 mb-12">Detail Acara</h2>
              <div class="grid md:grid-cols-2 gap-8">
                <!-- Akad -->
                <div class="bg-white rounded-xl shadow-lg p-8">
                  <h3 class="text-2xl font-bold text-green-600 mb-4">Akad Nikah</h3>
                  <div class="space-y-3 text-gray-700">
                    <div class="flex items-start">
                      <svg class="w-5 h-5 mr-2 mt-0.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      <div>
                        <p class="font-semibold">{{ new Date(invitation.content.akad_datetime).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
                        <p>{{ new Date(invitation.content.akad_datetime).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }} WIB</p>
                      </div>
                    </div>
                    <div class="flex items-start">
                      <svg class="w-5 h-5 mr-2 mt-0.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                      <p>{{ invitation.content.akad_venue }}</p>
                    </div>
                    <a
                      v-if="invitation.content.akad_maps_url"
                      :href="invitation.content.akad_maps_url"
                      target="_blank"
                      class="inline-block bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-green-700 transition mt-2"
                    >
                      Lihat Lokasi
                    </a>
                  </div>
                </div>

                <!-- Reception -->
                <div v-if="invitation.content.reception_datetime" class="bg-white rounded-xl shadow-lg p-8">
                  <h3 class="text-2xl font-bold text-green-600 mb-4">Resepsi</h3>
                  <div class="space-y-3 text-gray-700">
                    <div class="flex items-start">
                      <svg class="w-5 h-5 mr-2 mt-0.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      <div>
                        <p class="font-semibold">{{ new Date(invitation.content.reception_datetime).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
                        <p>{{ new Date(invitation.content.reception_datetime).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }} WIB</p>
                      </div>
                    </div>
                    <div class="flex items-start">
                      <svg class="w-5 h-5 mr-2 mt-0.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                      <p>{{ invitation.content.reception_venue }}</p>
                    </div>
                    <a
                      v-if="invitation.content.reception_maps_url"
                      :href="invitation.content.reception_maps_url"
                      target="_blank"
                      class="inline-block bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-green-700 transition mt-2"
                    >
                      Lihat Lokasi
                    </a>
                  </div>
                </div>
              </div>
            </div>

            <!-- Love Story -->
            <div v-if="invitation.content.love_story" class="max-w-3xl mx-auto mb-16">
              <h2 class="text-3xl font-bold text-center text-gray-900 mb-8">Cerita Cinta Kami</h2>
              <div class="bg-white rounded-xl shadow-lg p-8">
                <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ invitation.content.love_story }}</p>
              </div>
            </div>

            <!-- Special Message -->
            <div v-if="invitation.content.special_message" class="max-w-3xl mx-auto mb-16">
              <div class="bg-gradient-to-r from-green-100 to-emerald-100 rounded-xl p-8 text-center">
                <p class="text-gray-800 text-lg italic">{{ invitation.content.special_message }}</p>
              </div>
            </div>

            <!-- Preview Note -->
            <div class="max-w-3xl mx-auto text-center">
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <svg class="w-12 h-12 mx-auto text-blue-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-lg font-semibold text-blue-900 mb-2">Mode Preview</h3>
                <p class="text-blue-800 text-sm">
                  Ini adalah preview undangan Anda. Template lengkap dengan semua sections dan ornaments akan ditampilkan setelah dipublikasikan.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
