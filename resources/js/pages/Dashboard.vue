<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

// Disable default layout
defineOptions({
  layout: undefined
})

interface InvitationData {
  id: number
  status: string
  subdomain: string
  custom_domain: string | null
  url: string
  published_at: string | null
  template: {
    name: string
    slug: string
  } | null
}

interface Stats {
  total_invitations: number
  total_guests: number
  confirmed_rsvps: number
  total_views: number
}

interface Analytics {
  total_views: number
  total_guests: number
  total_rsvp: number
  rsvp_attending: number
  rsvp_not_attending: number
  total_pax: number
  total_wishes: number
  total_gallery_photos: number
}

interface RecentRsvp {
  id: number
  name: string
  attendance: string
  pax_count: number
  message: string | null
  created_at: string
}

interface RecentWish {
  id: number
  name: string
  message: string
  created_at: string
}

const props = defineProps<{
  stats: Stats
  invitation: InvitationData | null
  analytics: Analytics | null
  recentRsvps: RecentRsvp[]
  recentWishes: RecentWish[]
}>()

const showUserMenu = ref(false)
const showQuickStartGuide = ref(true) // Show by default for new users

const logout = () => {
  router.post('/logout')
}

const publishInvitation = () => {
  router.post('/dashboard/publish')
}

const unpublishInvitation = () => {
  if (confirm('Yakin ingin unpublish undangan? Tamu tidak akan bisa mengakses undangan.')) {
    router.post('/dashboard/unpublish')
  }
}

const hasInvitation = computed(() => props.invitation !== null)
const isPublished = computed(() => props.invitation?.status === 'published')
</script>

<template>
  <div>
    <Head title="Dashboard" />

    <div class="min-h-screen bg-gray-50">
      <!-- Navigation -->
      <nav class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-4">
          <div class="flex items-center justify-between h-16">
            <!-- Logo -->
            <Link href="/dashboard" class="flex items-center space-x-2">
              <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-purple-600 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-xl">M</span>
              </div>
              <span class="text-2xl font-bold bg-gradient-to-r from-pink-600 to-purple-600 bg-clip-text text-transparent">
                MyAkad
              </span>
            </Link>

            <!-- Navigation Links -->
            <div class="hidden md:flex items-center space-x-8">
              <Link
                href="/dashboard"
                class="text-pink-600 font-medium transition"
              >
                Dashboard
              </Link>
              <Link
                v-if="hasInvitation"
                href="/dashboard/editor"
                class="text-gray-700 hover:text-pink-600 font-medium transition"
              >
                Editor
              </Link>
              <Link
                v-if="hasInvitation"
                href="/dashboard/customize"
                class="text-gray-700 hover:text-pink-600 font-medium transition"
              >
                Kustomisasi
              </Link>
              <Link
                v-if="hasInvitation"
                href="/dashboard/gallery"
                class="text-gray-700 hover:text-pink-600 font-medium transition"
              >
                Galeri
              </Link>
              <Link
                v-if="hasInvitation"
                href="/dashboard/guests"
                class="text-gray-700 hover:text-pink-600 font-medium transition"
              >
                Tamu
              </Link>
              <Link
                href="/templates"
                class="text-gray-700 hover:text-pink-600 font-medium transition"
              >
                Template
              </Link>
            </div>

            <!-- User Menu -->
            <div class="relative">
              <button
                @click="showUserMenu = !showUserMenu"
                class="flex items-center space-x-3 focus:outline-none"
              >
                <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-purple-600 rounded-full flex items-center justify-center">
                  <span class="text-white font-semibold text-sm">
                    {{ $page.props.auth?.user?.name?.charAt(0).toUpperCase() || 'U' }}
                  </span>
                </div>
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              <!-- Dropdown Menu -->
              <div
                v-show="showUserMenu"
                @click="showUserMenu = false"
                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50"
              >
                <div class="px-4 py-2 border-b border-gray-100">
                  <p class="text-sm font-semibold text-gray-900">{{ $page.props.auth?.user?.name }}</p>
                  <p class="text-xs text-gray-600">{{ $page.props.auth?.user?.email }}</p>
                </div>
                
                <Link
                  href="/settings/profile"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition"
                >
                  <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Profil
                  </div>
                </Link>

                <Link
                  href="/settings/security"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition"
                >
                  <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Keamanan
                  </div>
                </Link>

                <div class="border-t border-gray-100 mt-2 pt-2">
                  <button
                    @click="logout"
                    class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition"
                  >
                    <div class="flex items-center">
                      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                      </svg>
                      Keluar
                    </div>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </nav>

    <div class="min-h-screen bg-gradient-to-br from-pink-50 via-white to-purple-50 py-8">
      <div class="container mx-auto px-4">
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-gray-600 mt-1">Kelola undangan digital Anda</p>
          </div>
          <Link
            href="/templates"
            class="bg-gradient-to-r from-pink-600 to-purple-600 text-white px-6 py-3 rounded-full font-semibold hover:shadow-lg transition"
          >
            + Buat Undangan Baru
          </Link>
        </div>

        <!-- Flash Messages -->
        <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 flex items-center">
          <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          {{ $page.props.flash.success }}
        </div>

        <div v-if="$page.props.flash?.error" class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6 flex items-center">
          <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
          {{ $page.props.flash.error }}
        </div>

        <!-- Quick Start Guide -->
        <div v-if="hasInvitation" class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl shadow-xl p-6 mb-8 text-white">
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="flex items-center mb-3">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
                <h3 class="text-xl font-bold">Panduan Cepat</h3>
                <button
                  @click="showQuickStartGuide = !showQuickStartGuide"
                  class="ml-auto text-white hover:text-blue-100 transition"
                >
                  <svg v-if="showQuickStartGuide" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                  </svg>
                  <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>
              </div>
              
              <div v-if="showQuickStartGuide" class="space-y-4">
                <p class="text-blue-100">Ikuti langkah-langkah berikut untuk menyelesaikan undangan Anda:</p>
                
                <div class="grid md:grid-cols-2 gap-4">
                  <!-- Step 1 -->
                  <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                    <div class="flex items-start">
                      <div class="flex-shrink-0 w-8 h-8 bg-white text-blue-600 rounded-full flex items-center justify-center font-bold mr-3">
                        1
                      </div>
                      <div class="flex-1">
                        <h4 class="font-semibold mb-1">Isi Konten Undangan</h4>
                        <p class="text-sm text-blue-100 mb-2">Lengkapi data mempelai, acara, dan informasi lainnya</p>
                        <Link
                          href="/dashboard/editor"
                          class="inline-flex items-center text-sm font-medium hover:underline"
                        >
                          Buka Editor
                          <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                          </svg>
                        </Link>
                      </div>
                    </div>
                  </div>

                  <!-- Step 2 -->
                  <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                    <div class="flex items-start">
                      <div class="flex-shrink-0 w-8 h-8 bg-white text-blue-600 rounded-full flex items-center justify-center font-bold mr-3">
                        2
                      </div>
                      <div class="flex-1">
                        <h4 class="font-semibold mb-1">Atur Subdomain</h4>
                        <p class="text-sm text-blue-100 mb-2">Pilih alamat unik untuk undangan Anda</p>
                        <Link
                          href="/dashboard/settings"
                          class="inline-flex items-center text-sm font-medium hover:underline"
                        >
                          Atur Subdomain
                          <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                          </svg>
                        </Link>
                      </div>
                    </div>
                  </div>

                  <!-- Step 3 -->
                  <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                    <div class="flex items-start">
                      <div class="flex-shrink-0 w-8 h-8 bg-white text-blue-600 rounded-full flex items-center justify-center font-bold mr-3">
                        3
                      </div>
                      <div class="flex-1">
                        <h4 class="font-semibold mb-1">Kustomisasi Tampilan</h4>
                        <p class="text-sm text-blue-100 mb-2">Atur urutan section dan ornamen</p>
                        <Link
                          href="/dashboard/customize"
                          class="inline-flex items-center text-sm font-medium hover:underline"
                        >
                          Kustomisasi
                          <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                          </svg>
                        </Link>
                      </div>
                    </div>
                  </div>

                  <!-- Step 4 -->
                  <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                    <div class="flex items-start">
                      <div class="flex-shrink-0 w-8 h-8 bg-white text-blue-600 rounded-full flex items-center justify-center font-bold mr-3">
                        4
                      </div>
                      <div class="flex-1">
                        <h4 class="font-semibold mb-1">Publikasikan</h4>
                        <p class="text-sm text-blue-100 mb-2">Bagikan undangan ke tamu Anda</p>
                        <Link
                          href="/dashboard/settings"
                          class="inline-flex items-center text-sm font-medium hover:underline"
                        >
                          Publikasikan
                          <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                          </svg>
                        </Link>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Additional Tips -->
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                  <h4 class="font-semibold mb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Tips Berguna
                  </h4>
                  <ul class="text-sm text-blue-100 space-y-1 ml-7">
                    <li>• Gunakan tombol <strong>Preview</strong> untuk melihat hasil undangan</li>
                    <li>• Upload foto mempelai untuk tampilan lebih personal</li>
                    <li>• Tambahkan galeri foto untuk kenangan indah</li>
                    <li>• Jangan lupa isi amplop digital untuk terima hadiah</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid md:grid-cols-4 gap-6 mb-8">
          <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                </svg>
              </div>
            </div>
            <div class="text-3xl font-bold text-gray-900 mb-1">{{ stats.total_invitations }}</div>
            <div class="text-sm text-gray-600">Total Undangan</div>
          </div>

          <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
              </div>
            </div>
            <div class="text-3xl font-bold text-gray-900 mb-1">{{ stats.total_guests }}</div>
            <div class="text-sm text-gray-600">Total Tamu</div>
          </div>

          <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
            <div class="text-3xl font-bold text-gray-900 mb-1">{{ stats.confirmed_rsvps }}</div>
            <div class="text-sm text-gray-600">Konfirmasi Hadir</div>
          </div>

          <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </div>
            </div>
            <div class="text-3xl font-bold text-gray-900 mb-1">{{ stats.total_views }}</div>
            <div class="text-sm text-gray-600">Total Views</div>
          </div>
        </div>

        <!-- Invitation Status Card -->
        <div v-if="hasInvitation" class="bg-white rounded-xl shadow-md p-6 mb-8">
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="flex items-center mb-3">
                <h2 class="text-xl font-bold text-gray-900 mr-3">Undangan Anda</h2>
                <span 
                  class="px-3 py-1 rounded-full text-xs font-semibold"
                  :class="isPublished ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                >
                  {{ isPublished ? 'Published' : 'Draft' }}
                </span>
              </div>
              
              <div class="space-y-2 mb-4">
                <div class="flex items-center text-sm text-gray-600">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                  </svg>
                  Template: {{ invitation.template?.name || 'Tidak ada template' }}
                </div>
                
                <div v-if="isPublished" class="flex items-center text-sm text-gray-600">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                  </svg>
                  <a :href="invitation.url" target="_blank" class="text-pink-600 hover:text-pink-700 font-medium">
                    {{ invitation.url }}
                  </a>
                </div>
                
                <div v-if="invitation.published_at" class="flex items-center text-sm text-gray-600">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  Dipublikasikan: {{ invitation.published_at }}
                </div>
              </div>

              <div class="flex space-x-3">
                <Link
                  href="/dashboard/editor"
                  class="bg-gradient-to-r from-pink-600 to-purple-600 text-white px-6 py-2 rounded-lg font-semibold hover:shadow-lg transition"
                >
                  Edit Konten
                </Link>
                <Link
                  href="/dashboard/customize"
                  class="border-2 border-pink-600 text-pink-600 px-6 py-2 rounded-lg font-semibold hover:bg-pink-50 transition"
                >
                  Kustomisasi
                </Link>
                <a
                  v-if="isPublished"
                  :href="invitation.url"
                  target="_blank"
                  class="border-2 border-gray-300 text-gray-700 px-6 py-2 rounded-lg font-semibold hover:border-gray-400 transition"
                >
                  Lihat Undangan
                </a>
                <button
                  v-if="isPublished"
                  @click="unpublishInvitation"
                  class="border-2 border-red-600 text-red-600 px-6 py-2 rounded-lg font-semibold hover:bg-red-50 transition"
                >
                  Unpublish
                </button>
                <button
                  v-else
                  @click="publishInvitation"
                  class="border-2 border-green-600 text-green-600 px-6 py-2 rounded-lg font-semibold hover:bg-green-50 transition"
                >
                  Publikasikan
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Analytics & Recent Activity -->
        <div v-if="hasInvitation && analytics" class="grid md:grid-cols-2 gap-6 mb-8">
          <!-- Recent RSVPs -->
          <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-bold text-gray-900">RSVP Terbaru</h3>
              <Link href="/dashboard/guests" class="text-sm text-pink-600 hover:text-pink-700 font-medium">
                Lihat Semua →
              </Link>
            </div>

            <div v-if="recentRsvps.length > 0" class="space-y-3">
              <div
                v-for="rsvp in recentRsvps"
                :key="rsvp.id"
                class="flex items-start justify-between p-3 bg-gray-50 rounded-lg"
              >
                <div class="flex-1">
                  <p class="font-semibold text-gray-900">{{ rsvp.name }}</p>
                  <div class="flex items-center mt-1 space-x-2">
                    <span
                      class="px-2 py-0.5 rounded-full text-xs font-medium"
                      :class="rsvp.attendance === 'yes' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                    >
                      {{ rsvp.attendance === 'yes' ? 'Hadir' : 'Tidak Hadir' }}
                    </span>
                    <span v-if="rsvp.attendance === 'yes'" class="text-xs text-gray-600">
                      {{ rsvp.pax_count }} orang
                    </span>
                  </div>
                  <p v-if="rsvp.message" class="text-xs text-gray-600 mt-1">{{ rsvp.message }}</p>
                </div>
                <span class="text-xs text-gray-500">{{ rsvp.created_at }}</span>
              </div>
            </div>

            <div v-else class="text-center py-8 text-gray-500">
              <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <p class="text-sm">Belum ada RSVP</p>
            </div>
          </div>

          <!-- Recent Wishes -->
          <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-bold text-gray-900">Ucapan & Doa Terbaru</h3>
              <Link href="/dashboard/guests" class="text-sm text-pink-600 hover:text-pink-700 font-medium">
                Lihat Semua →
              </Link>
            </div>

            <div v-if="recentWishes.length > 0" class="space-y-3">
              <div
                v-for="wish in recentWishes"
                :key="wish.id"
                class="p-3 bg-gray-50 rounded-lg"
              >
                <div class="flex items-start justify-between mb-2">
                  <p class="font-semibold text-gray-900">{{ wish.name }}</p>
                  <span class="text-xs text-gray-500">{{ wish.created_at }}</span>
                </div>
                <p class="text-sm text-gray-700">{{ wish.message }}</p>
              </div>
            </div>

            <div v-else class="text-center py-8 text-gray-500">
              <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
              </svg>
              <p class="text-sm">Belum ada ucapan</p>
            </div>
          </div>
        </div>

        <!-- Detailed Analytics -->
        <div v-if="hasInvitation && analytics" class="bg-white rounded-xl shadow-md p-6 mb-8">
          <h3 class="text-lg font-bold text-gray-900 mb-6">Statistik Detail</h3>
          
          <div class="grid md:grid-cols-4 gap-6">
            <div class="text-center">
              <div class="text-3xl font-bold text-pink-600 mb-1">{{ analytics.total_rsvp }}</div>
              <div class="text-sm text-gray-600">Total RSVP</div>
            </div>

            <div class="text-center">
              <div class="text-3xl font-bold text-green-600 mb-1">{{ analytics.rsvp_attending }}</div>
              <div class="text-sm text-gray-600">Hadir</div>
            </div>

            <div class="text-center">
              <div class="text-3xl font-bold text-red-600 mb-1">{{ analytics.rsvp_not_attending }}</div>
              <div class="text-sm text-gray-600">Tidak Hadir</div>
            </div>

            <div class="text-center">
              <div class="text-3xl font-bold text-purple-600 mb-1">{{ analytics.total_pax }}</div>
              <div class="text-sm text-gray-600">Total Tamu Hadir</div>
            </div>
          </div>

          <div class="mt-6 pt-6 border-t border-gray-200">
            <div class="grid md:grid-cols-3 gap-6">
              <div class="text-center">
                <div class="text-2xl font-bold text-blue-600 mb-1">{{ analytics.total_wishes }}</div>
                <div class="text-sm text-gray-600">Ucapan & Doa</div>
              </div>

              <div class="text-center">
                <div class="text-2xl font-bold text-amber-600 mb-1">{{ analytics.total_gallery_photos }}</div>
                <div class="text-sm text-gray-600">Foto Galeri</div>
              </div>

              <div class="text-center">
                <div class="text-2xl font-bold text-indigo-600 mb-1">{{ analytics.total_views }}</div>
                <div class="text-sm text-gray-600">Total Views</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white rounded-xl shadow-md p-12 text-center">
          <div class="max-w-md mx-auto">
            <div class="w-24 h-24 bg-gradient-to-br from-pink-100 to-purple-100 rounded-full mx-auto mb-6 flex items-center justify-center">
              <svg class="w-12 h-12 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
              </svg>
            </div>
            
            <h2 class="text-2xl font-bold text-gray-900 mb-3">
              Belum Ada Undangan
            </h2>
            <p class="text-gray-600 mb-8">
              Mulai buat undangan digital pertama Anda dengan memilih template yang sesuai dengan tema pernikahan Anda.
            </p>

            <div class="space-y-4">
              <Link
                href="/templates"
                class="inline-block bg-gradient-to-r from-pink-600 to-purple-600 text-white px-8 py-4 rounded-full font-semibold text-lg hover:shadow-xl transition transform hover:scale-105"
              >
                Pilih Template
              </Link>

              <div class="flex items-center justify-center space-x-8 text-sm text-gray-600">
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  <span>Mudah digunakan</span>
                </div>
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  <span>Siap dalam 5 menit</span>
                </div>
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  <span>Harga terjangkau</span>
                </div>
              </div>
            </div>
            </div>
          </div>

        <!-- Quick Actions -->
        <div class="grid md:grid-cols-3 gap-6 mt-8">
          <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition cursor-pointer">
            <div class="flex items-start">
              <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center mr-4">
                <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
              </div>
              <div>
                <h3 class="font-semibold text-gray-900 mb-1">Panduan Lengkap</h3>
                <p class="text-sm text-gray-600">Pelajari cara membuat undangan digital yang sempurna</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition cursor-pointer">
            <div class="flex items-start">
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <h3 class="font-semibold text-gray-900 mb-1">Bantuan & Support</h3>
                <p class="text-sm text-gray-600">Butuh bantuan? Tim kami siap membantu Anda</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition cursor-pointer">
            <div class="flex items-start">
              <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
              </div>
              <div>
                <h3 class="font-semibold text-gray-900 mb-1">Fitur Premium</h3>
                <p class="text-sm text-gray-600">Upgrade untuk mendapatkan fitur tambahan</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    </div>
  </div>
</template>
