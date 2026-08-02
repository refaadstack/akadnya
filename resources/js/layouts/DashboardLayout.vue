<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import {
  BookOpen,
  CalendarCheck,
  ChevronDown,
  Image,
  LayoutDashboard,
  LockKeyhole,
  LogOut,
  Palette,
  PenLine,
  Send,
  Users,
} from 'lucide-vue-next'
import { computed, ref } from 'vue'
import type { Component } from 'vue'

interface NavItem {
  label: string
  href: string
  icon: Component
}

const showUserMenu = ref(false)
const showMobileMenu = ref(false)

const navItems = computed<NavItem[]>(() => [
  { label: 'Dashboard', href: '/dashboard', icon: LayoutDashboard },
  { label: 'Editor', href: '/dashboard/editor', icon: PenLine },
  { label: 'Love Story', href: '/dashboard/love-story', icon: BookOpen },
  { label: 'Kustomisasi', href: '/dashboard/customize', icon: Palette },
  { label: 'Galeri', href: '/dashboard/gallery', icon: Image },
  { label: 'Tamu', href: '/dashboard/guests', icon: Users },
  { label: 'RSVP', href: '/dashboard/rsvp', icon: CalendarCheck },
])

const isActive = (href: string): boolean => {
  if (href === '/dashboard') {
    return window.location.pathname === '/dashboard'
  }
  return window.location.pathname.startsWith(href)
}

const logout = () => {
  router.post('/logout')
}
</script>

<template>
  <div class="min-h-screen bg-[var(--my-background)]">
    <!-- Navigation -->
    <nav class="sticky top-0 z-40 border-b border-[var(--my-border)]/70 bg-[var(--my-background)]/88 backdrop-blur-md">
      <div class="my-container">
        <div class="flex min-h-16 items-center justify-between gap-5">
          <!-- Logo -->
          <Link href="/dashboard" class="font-display text-3xl font-bold leading-none text-[var(--my-primary)]">
            MyAkad
          </Link>

          <!-- Desktop Nav -->
          <div class="hidden items-center gap-1 lg:flex">
            <Link
              v-for="item in navItems"
              :key="item.href"
              :href="item.href"
              class="relative px-4 py-2 text-sm font-semibold transition"
              :class="isActive(item.href)
                ? 'text-[var(--my-primary)]'
                : 'text-[var(--my-muted)] hover:text-[var(--my-primary)]'"
            >
              {{ item.label }}
              <span
                v-if="isActive(item.href)"
                class="absolute bottom-0 left-0 right-0 h-0.5 bg-[var(--my-primary)]"
              />
            </Link>
          </div>

          <!-- Mobile Menu Button -->
          <button
            type="button"
            class="lg:hidden"
            @click="showMobileMenu = !showMobileMenu"
          >
            <svg class="size-6 text-[var(--my-neutral)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="!showMobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <!-- User Menu (Desktop) -->
          <div class="relative hidden lg:block">
            <button type="button" class="flex items-center gap-3" @click="showUserMenu = !showUserMenu">
              <span class="grid size-10 place-items-center rounded-lg bg-[var(--my-primary)] text-sm font-bold text-white">
                {{ $page.props.auth?.user?.name?.charAt(0).toUpperCase() || 'U' }}
              </span>
              <ChevronDown class="size-4 text-[var(--my-muted)]" />
            </button>

            <div
              v-show="showUserMenu"
              class="my-card absolute right-0 mt-3 w-56 overflow-hidden py-2"
              @click="showUserMenu = false"
            >
              <div class="border-b border-[var(--my-border)] px-4 py-3">
                <p class="truncate text-sm font-bold text-[var(--my-neutral)]">{{ $page.props.auth?.user?.name }}</p>
                <p class="truncate text-xs text-[var(--my-muted)]">{{ $page.props.auth?.user?.email }}</p>
              </div>
              <Link href="/settings/profile" class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-[var(--my-muted)] transition hover:bg-[var(--my-surface-soft)]">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Profil
              </Link>
              <Link href="/settings/security" class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-[var(--my-muted)] transition hover:bg-[var(--my-surface-soft)]">
                <LockKeyhole class="size-4" />
                Keamanan
              </Link>
              <button type="button" class="flex w-full items-center gap-2 border-t border-[var(--my-border)] px-4 py-2 text-left text-sm font-semibold text-red-600 transition hover:bg-red-50" @click="logout">
                <LogOut class="size-4" />
                Keluar
              </button>
            </div>
          </div>
        </div>

        <!-- Mobile Nav -->
        <div v-if="showMobileMenu" class="border-t border-[var(--my-border)] py-4 lg:hidden">
          <div class="flex flex-col gap-1">
            <Link
              v-for="item in navItems"
              :key="item.href"
              :href="item.href"
              class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-semibold transition"
              :class="isActive(item.href)
                ? 'bg-[var(--my-primary)]/10 text-[var(--my-primary)]'
                : 'text-[var(--my-muted)] hover:bg-[var(--my-surface-soft)] hover:text-[var(--my-primary)]'"
              @click="showMobileMenu = false"
            >
              <component :is="item.icon" class="size-5" />
              {{ item.label }}
            </Link>
          </div>
          <div class="mt-4 border-t border-[var(--my-border)] pt-4">
            <div class="mb-3 px-4">
              <p class="text-sm font-bold text-[var(--my-neutral)]">{{ $page.props.auth?.user?.name }}</p>
              <p class="text-xs text-[var(--my-muted)]">{{ $page.props.auth?.user?.email }}</p>
            </div>
            <Link href="/settings/profile" class="flex items-center gap-3 px-4 py-2 text-sm font-semibold text-[var(--my-muted)] transition hover:text-[var(--my-primary)]">
              <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              Profil
            </Link>
            <Link href="/settings/security" class="flex items-center gap-3 px-4 py-2 text-sm font-semibold text-[var(--my-muted)] transition hover:text-[var(--my-primary)]">
              <LockKeyhole class="size-4" />
              Keamanan
            </Link>
            <button type="button" class="flex w-full items-center gap-3 px-4 py-2 text-left text-sm font-semibold text-red-600 transition hover:bg-red-50" @click="logout">
              <LogOut class="size-4" />
              Keluar
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main>
      <slot />
    </main>
  </div>
</template>
