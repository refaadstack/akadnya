<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import {
  BarChart3,
  BookOpen,
  CalendarCheck,
  ChevronDown,
  ChevronUp,
  CircleHelp,
  Eye,
  Image,
  LayoutDashboard,
  Link2,
  MessageCircle,
  Palette,
  Send,
  Sparkles,
  Users,
} from 'lucide-vue-next'
import { computed, ref } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { Component } from 'vue'

defineOptions({
  layout: undefined,
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

interface InvitationOption {
  id: number
  status: string
  subdomain: string | null
  custom_domain: string | null
  url: string
  is_active: boolean
  template: {
    id: number
    name: string
    slug: string
    thumbnail_url: string | null
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

interface StatCard {
  label: string
  value: number
  icon: Component
}

const props = defineProps<{
  stats: Stats
  invitation: InvitationData | null
  analytics: Analytics | null
  recentRsvps: RecentRsvp[]
  recentWishes: RecentWish[]
  invitationOptions: InvitationOption[]
}>()

const showQuickStartGuide = ref(true)

const hasInvitation = computed(() => props.invitation !== null)
const isPublished = computed(() => props.invitation?.status === 'published')

const statCards = computed<StatCard[]>(() => [
  { label: 'Total Undangan', value: props.stats.total_invitations, icon: LayoutDashboard },
  { label: 'Total Tamu', value: props.stats.total_guests, icon: Users },
  { label: 'Konfirmasi Hadir', value: props.stats.confirmed_rsvps, icon: CalendarCheck },
  { label: 'Total Views', value: props.stats.total_views, icon: Eye },
])

const quickSteps = [
  { title: 'Isi Konten Undangan', text: 'Lengkapi data mempelai, acara, dan informasi penting.', href: '/dashboard/editor', icon: BookOpen },
  { title: 'Atur Subdomain', text: 'Pilih alamat unik yang mudah dibagikan ke tamu.', href: '/dashboard/settings', icon: Link2 },
  { title: 'Kustomisasi Tampilan', text: 'Rapikan urutan section, warna, dan ornamen.', href: '/dashboard/customize', icon: Palette },
  { title: 'Publikasikan', text: 'Cek preview lalu bagikan undangan saat sudah siap.', href: '/dashboard/settings', icon: Send },
]

const quickActions = [
  { title: 'Panduan Lengkap', text: 'Pelajari alur membuat undangan dari template sampai publish.', icon: BookOpen },
  { title: 'Bantuan & Support', text: 'Butuh bantuan saat setup? Tim MyAkad siap membantu.', icon: CircleHelp },
  { title: 'Fitur Premium', text: 'Kelola galeri, RSVP, amplop digital, dan detail personal.', icon: Sparkles },
]

const publishInvitation = () => {
  router.post('/dashboard/publish')
}

const unpublishInvitation = () => {
  if (confirm('Yakin ingin unpublish undangan? Tamu tidak akan bisa mengakses undangan.')) {
    router.post('/dashboard/unpublish')
  }
}

const selectInvitation = (invitationId: number) => {
  router.post(
    `/dashboard/invitations/${invitationId}/select`,
    {},
    {
      preserveScroll: true,
    },
  )
}
</script>

<template>
  <DashboardLayout>
    <Head title="Dashboard" />

    <main class="my-container py-10">
      <section class="mb-8 flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
        <div>
          <p class="my-label mb-3">Ruang Kerja Undangan</p>
          <h1 class="my-heading text-4xl md:text-5xl">Dashboard</h1>
          <p class="my-copy mt-3">Kelola template, konten, tamu, RSVP, dan publikasi undanganmu.</p>
        </div>
        <Link href="/templates" class="my-btn-primary w-fit">
          Buat Undangan Baru
        </Link>
      </section>

      <div v-if="$page.props.flash?.success" class="mb-6 rounded-lg border border-[var(--my-primary)]/25 bg-[var(--my-primary)]/10 px-4 py-3 font-semibold text-[var(--my-neutral)]">
        {{ $page.props.flash.success }}
      </div>

      <div v-if="$page.props.flash?.error" class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 font-semibold text-red-700">
        {{ $page.props.flash.error }}
      </div>

      <section v-if="invitationOptions.length > 0" class="my-card mb-8 p-6">
        <div class="mb-5 flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
          <div>
            <h2 class="my-heading text-2xl">Template yang kamu miliki</h2>
            <p class="mt-1 text-[var(--my-muted)]">Pilih template aktif sebelum mengedit, preview, atau publish undangan.</p>
          </div>
          <span class="text-sm font-bold text-[var(--my-primary)]">{{ invitationOptions.length }} template tersedia</span>
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          <button
            v-for="option in invitationOptions"
            :key="option.id"
            type="button"
            class="rounded-lg border p-4 text-left transition hover:-translate-y-0.5 hover:border-[var(--my-primary)]"
            :class="option.is_active ? 'border-[var(--my-primary)] bg-[var(--my-primary)]/8' : 'border-[var(--my-border)] bg-white/60'"
            @click="selectInvitation(option.id)"
          >
            <div class="flex gap-4">
              <div class="grid h-20 w-16 shrink-0 place-items-center overflow-hidden rounded-lg bg-[var(--my-surface-soft)]">
                <img
                  v-if="option.template?.thumbnail_url"
                  :src="option.template.thumbnail_url"
                  :alt="option.template.name"
                  class="h-full w-full object-cover"
                />
                <span v-else class="font-display text-xl font-bold text-[var(--my-primary)]">My</span>
              </div>
              <div class="min-w-0 flex-1">
                <div class="mb-1 flex items-center gap-2">
                  <h3 class="truncate font-bold text-[var(--my-neutral)]">{{ option.template?.name || 'Template' }}</h3>
                  <span v-if="option.is_active" class="rounded-full bg-[var(--my-primary)]/12 px-2 py-0.5 text-xs font-bold text-[var(--my-primary)]">
                    Aktif
                  </span>
                </div>
                <p class="truncate text-xs text-[var(--my-muted)]">{{ option.template?.slug }}</p>
                <p class="mt-2 text-xs font-semibold text-[var(--my-muted)]">
                  {{ option.status === 'published' ? 'Published' : 'Draft' }}
                </p>
              </div>
            </div>
          </button>
        </div>
      </section>

      <section v-if="hasInvitation" class="my-card mb-8 p-6">
        <div class="mb-4 flex items-center gap-3">
          <Sparkles class="size-6 text-[var(--my-primary)]" />
          <h2 class="my-heading text-2xl">Panduan Cepat</h2>
          <button type="button" class="ml-auto text-[var(--my-muted)] transition hover:text-[var(--my-primary)]" @click="showQuickStartGuide = !showQuickStartGuide">
            <ChevronUp v-if="showQuickStartGuide" class="size-5" />
            <ChevronDown v-else class="size-5" />
          </button>
        </div>

        <div v-if="showQuickStartGuide" class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <Link v-for="step in quickSteps" :key="step.title" :href="step.href" class="rounded-lg border border-[var(--my-border)] bg-white/55 p-4 transition hover:-translate-y-0.5 hover:border-[var(--my-primary)]">
            <component :is="step.icon" class="mb-4 size-6 text-[var(--my-primary)]" />
            <h3 class="font-bold text-[var(--my-neutral)]">{{ step.title }}</h3>
            <p class="mt-2 text-sm leading-6 text-[var(--my-muted)]">{{ step.text }}</p>
          </Link>
        </div>
      </section>

      <section class="mb-8 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
        <article v-for="card in statCards" :key="card.label" class="my-card p-6">
          <component :is="card.icon" class="mb-5 size-7 text-[var(--my-primary)]" />
          <p class="text-3xl font-bold text-[var(--my-neutral)]">{{ card.value }}</p>
          <p class="mt-1 text-sm font-semibold text-[var(--my-muted)]">{{ card.label }}</p>
        </article>
      </section>

      <section v-if="hasInvitation" class="my-card mb-8 p-6">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
          <div>
            <div class="mb-3 flex flex-wrap items-center gap-3">
              <h2 class="my-heading text-2xl">Undangan kamu</h2>
              <span class="rounded-full px-3 py-1 text-xs font-bold" :class="isPublished ? 'bg-[var(--my-primary)]/12 text-[var(--my-primary)]' : 'bg-[var(--my-secondary)]/20 text-[#8b5b52]'">
                {{ isPublished ? 'Published' : 'Draft' }}
              </span>
            </div>

            <div class="grid gap-2 text-sm text-[var(--my-muted)]">
              <p>Template: <span class="font-bold text-[var(--my-neutral)]">{{ invitation?.template?.name || 'Tidak ada template' }}</span></p>
              <p v-if="isPublished">
                Link:
                <a :href="invitation?.url" target="_blank" class="font-bold text-[var(--my-primary)]">{{ invitation?.url }}</a>
              </p>
              <p v-if="invitation?.published_at">Dipublikasikan: {{ invitation.published_at }}</p>
            </div>
          </div>

          <div class="flex flex-wrap gap-3">
            <Link href="/dashboard/editor" class="my-btn-primary">Edit Konten</Link>
            <Link href="/dashboard/customize" class="my-btn-secondary">Kustomisasi</Link>
            <a v-if="isPublished" :href="invitation?.url" target="_blank" class="my-btn-secondary">Lihat Undangan</a>
            <button v-if="isPublished" type="button" class="my-btn-secondary border-red-300 text-red-600 hover:bg-red-50" @click="unpublishInvitation">Unpublish</button>
            <button v-else type="button" class="my-btn-secondary border-[var(--my-primary)] text-[var(--my-primary)]" @click="publishInvitation">Publikasikan</button>
          </div>
        </div>
      </section>

      <section v-if="hasInvitation && analytics" class="mb-8 grid gap-6 lg:grid-cols-2">
        <article class="my-card p-6">
          <div class="mb-5 flex items-center justify-between gap-4">
            <h3 class="my-heading text-2xl">RSVP Terbaru</h3>
            <Link href="/dashboard/guests" class="text-sm font-bold text-[var(--my-primary)]">Lihat Semua</Link>
          </div>

          <div v-if="recentRsvps.length > 0" class="grid gap-3">
            <div v-for="rsvp in recentRsvps" :key="rsvp.id" class="rounded-lg border border-[var(--my-border)] bg-white/55 p-4">
              <div class="flex items-start justify-between gap-4">
                <div>
                  <p class="font-bold text-[var(--my-neutral)]">{{ rsvp.name }}</p>
                  <p class="mt-1 text-sm text-[var(--my-muted)]">
                    {{ rsvp.attendance === 'yes' ? `Hadir, ${rsvp.pax_count} orang` : 'Tidak hadir' }}
                  </p>
                </div>
                <span class="text-xs text-[var(--my-muted)]">{{ rsvp.created_at }}</span>
              </div>
              <p v-if="rsvp.message" class="mt-2 text-sm text-[var(--my-muted)]">{{ rsvp.message }}</p>
            </div>
          </div>
          <p v-else class="rounded-lg bg-[var(--my-surface-soft)] px-4 py-8 text-center text-[var(--my-muted)]">Belum ada RSVP.</p>
        </article>

        <article class="my-card p-6">
          <div class="mb-5 flex items-center justify-between gap-4">
            <h3 class="my-heading text-2xl">Ucapan & Doa</h3>
            <Link href="/dashboard/guests" class="text-sm font-bold text-[var(--my-primary)]">Lihat Semua</Link>
          </div>

          <div v-if="recentWishes.length > 0" class="grid gap-3">
            <div v-for="wish in recentWishes" :key="wish.id" class="rounded-lg border border-[var(--my-border)] bg-white/55 p-4">
              <div class="mb-2 flex items-start justify-between gap-4">
                <p class="font-bold text-[var(--my-neutral)]">{{ wish.name }}</p>
                <span class="text-xs text-[var(--my-muted)]">{{ wish.created_at }}</span>
              </div>
              <p class="text-sm leading-6 text-[var(--my-muted)]">{{ wish.message }}</p>
            </div>
          </div>
          <p v-else class="rounded-lg bg-[var(--my-surface-soft)] px-4 py-8 text-center text-[var(--my-muted)]">Belum ada ucapan.</p>
        </article>
      </section>

      <section v-if="hasInvitation && analytics" class="my-card mb-8 p-6">
        <h3 class="my-heading mb-6 text-2xl">Statistik Detail</h3>
        <div class="grid gap-5 md:grid-cols-3 xl:grid-cols-6">
          <div class="rounded-lg bg-white/55 p-4 text-center">
            <BarChart3 class="mx-auto mb-3 size-6 text-[var(--my-primary)]" />
            <p class="text-2xl font-bold text-[var(--my-neutral)]">{{ analytics.total_rsvp }}</p>
            <p class="text-sm text-[var(--my-muted)]">Total RSVP</p>
          </div>
          <div class="rounded-lg bg-white/55 p-4 text-center">
            <CalendarCheck class="mx-auto mb-3 size-6 text-[var(--my-primary)]" />
            <p class="text-2xl font-bold text-[var(--my-neutral)]">{{ analytics.rsvp_attending }}</p>
            <p class="text-sm text-[var(--my-muted)]">Hadir</p>
          </div>
          <div class="rounded-lg bg-white/55 p-4 text-center">
            <Users class="mx-auto mb-3 size-6 text-[var(--my-primary)]" />
            <p class="text-2xl font-bold text-[var(--my-neutral)]">{{ analytics.total_pax }}</p>
            <p class="text-sm text-[var(--my-muted)]">Tamu Hadir</p>
          </div>
          <div class="rounded-lg bg-white/55 p-4 text-center">
            <MessageCircle class="mx-auto mb-3 size-6 text-[var(--my-primary)]" />
            <p class="text-2xl font-bold text-[var(--my-neutral)]">{{ analytics.total_wishes }}</p>
            <p class="text-sm text-[var(--my-muted)]">Ucapan</p>
          </div>
          <div class="rounded-lg bg-white/55 p-4 text-center">
            <Image class="mx-auto mb-3 size-6 text-[var(--my-primary)]" />
            <p class="text-2xl font-bold text-[var(--my-neutral)]">{{ analytics.total_gallery_photos }}</p>
            <p class="text-sm text-[var(--my-muted)]">Foto</p>
          </div>
          <div class="rounded-lg bg-white/55 p-4 text-center">
            <Eye class="mx-auto mb-3 size-6 text-[var(--my-primary)]" />
            <p class="text-2xl font-bold text-[var(--my-neutral)]">{{ analytics.total_views }}</p>
            <p class="text-sm text-[var(--my-muted)]">Views</p>
          </div>
        </div>
      </section>

      <section v-if="!hasInvitation" class="my-card px-6 py-14 text-center">
        <Sparkles class="mx-auto mb-5 size-12 text-[var(--my-primary)]" />
        <h2 class="my-heading text-3xl">Belum ada undangan</h2>
        <p class="my-copy mx-auto mt-3 max-w-xl">
          Mulai dari koleksi template, lihat preview aslinya, lalu isi detail acara saat kamu sudah menemukan gaya yang cocok.
        </p>
        <Link href="/templates" class="my-btn-primary mt-8">Pilih Template</Link>
      </section>

      <section class="mt-8 grid gap-5 md:grid-cols-3">
        <article v-for="action in quickActions" :key="action.title" class="my-card p-6">
          <component :is="action.icon" class="mb-4 size-7 text-[var(--my-primary)]" />
          <h3 class="text-lg font-bold text-[var(--my-neutral)]">{{ action.title }}</h3>
          <p class="mt-2 text-sm leading-6 text-[var(--my-muted)]">{{ action.text }}</p>
        </article>
      </section>
    </main>
  </DashboardLayout>
</template>
