<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'

interface Entry {
  id: number
  event_type: 'check_in' | 'souvenir' | 'raffle'
  guest_id: number | null
  guest_name: string
  created_at: string
}

defineProps<{
  invitation: {
    id: number
    subdomain: string
    status: string
  }
  stats: {
    checked_in: number
    souvenirs: number
    raffles: number
  }
  entries: Entry[]
}>()

const checkInForm = useForm({
  code: '',
})

const souvenirForm = useForm({
  guest_id: null as number | null,
})

const raffleForm = useForm({})

const raffling = computed(() => raffleForm.processing)

const formatDate = (dateString: string) => {
  const date = new Date(dateString)

  return date.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const eventTypeLabels: Record<Entry['event_type'], string> = {
  check_in: 'Check-in',
  souvenir: 'Souvenir',
  raffle: 'Undian',
}

const eventTypeBadges: Record<Entry['event_type'], string> = {
  check_in: 'bg-blue-100 text-blue-800 border-blue-200',
  souvenir: 'bg-amber-100 text-amber-800 border-amber-200',
  raffle: 'bg-purple-100 text-purple-800 border-purple-200',
}

const submitCheckIn = () => {
  checkInForm.post('/dashboard/guest-book/check-in', {
    preserveScroll: true,
    onSuccess: () => {
      checkInForm.reset()
    },
  })
}

const submitSouvenir = (entry: Entry) => {
  souvenirForm.guest_id = entry.guest_id
  souvenirForm.post('/dashboard/guest-book/souvenir', {
    preserveScroll: true,
    onSuccess: () => {
      souvenirForm.reset()
    },
  })
}

const drawRaffle = () => {
  raffleForm.post('/dashboard/guest-book/raffle', {
    preserveScroll: true,
  })
}
</script>

<template>
  <DashboardLayout>
    <Head title="Buku Tamu" />

    <div class="space-y-6">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-[var(--my-neutral)]">Buku Tamu</h1>
          <p class="mt-1 text-sm text-[var(--my-muted)]">
            Check-in, souvenir, dan undian untuk acara Anda.
          </p>
        </div>
        <Link href="/dashboard/guest-book/scan" class="my-btn-primary inline-flex items-center gap-2">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 7V5a2 2 0 0 1 2-2h2" />
            <path d="M17 3h2a2 2 0 0 1 2 2v2" />
            <path d="M21 17v2a2 2 0 0 1-2 2h-2" />
            <path d="M7 21H5a2 2 0 0 1-2-2v-2" />
            <path d="M7 12h10" />
          </svg>
          Scan Barcode Tamu
        </Link>
      </div>

      <div v-if="$page.props.flash?.success" class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
        {{ $page.props.flash.success }}
      </div>
      <div v-if="$page.props.flash?.error" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
        {{ $page.props.flash.error }}
      </div>

      <section class="grid gap-4 sm:grid-cols-3">
        <article class="rounded-xl border border-[var(--my-border)] bg-white/60 p-6">
          <p class="text-sm text-[var(--my-muted)]">Check-in</p>
          <p class="mt-2 text-3xl font-bold text-[var(--my-primary)]">{{ stats.checked_in }}</p>
          <p class="mt-1 text-xs text-[var(--my-muted)]">tamu sudah check-in</p>
        </article>
        <article class="rounded-xl border border-[var(--my-border)] bg-white/60 p-6">
          <p class="text-sm text-[var(--my-muted)]">Souvenir</p>
          <p class="mt-2 text-3xl font-bold text-amber-600">{{ stats.souvenirs }}</p>
          <p class="mt-1 text-xs text-[var(--my-muted)]">souvenir sudah diambil</p>
        </article>
        <article class="rounded-xl border border-[var(--my-border)] bg-white/60 p-6">
          <p class="text-sm text-[var(--my-muted)]">Undian</p>
          <p class="mt-2 text-3xl font-bold text-purple-600">{{ stats.raffles }}</p>
          <p class="mt-1 text-xs text-[var(--my-muted)]">pemenang diundi</p>
        </article>
      </section>

      <section class="grid gap-6 lg:grid-cols-3">
        <article class="rounded-xl border border-[var(--my-border)] bg-white/60 p-6 lg:col-span-1">
          <h2 class="mb-4 font-bold text-[var(--my-neutral)]">Check-in Manual</h2>
          <form @submit.prevent="submitCheckIn">
            <label class="mb-1 block text-sm font-medium text-[var(--my-neutral)]">Kode Tamu</label>
            <input
              v-model="checkInForm.code"
              type="text"
              placeholder="Masukkan kode barcode tamu"
              class="mb-2 w-full rounded-lg border border-[var(--my-border)] px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-[var(--my-primary)]"
            />
            <p v-if="checkInForm.errors.code" class="mb-2 text-sm text-red-600">{{ checkInForm.errors.code }}</p>
            <button
              type="submit"
              :disabled="checkInForm.processing"
              class="my-btn-primary w-full"
            >
              {{ checkInForm.processing ? 'Memproses...' : 'Check-in' }}
            </button>
          </form>

          <div class="mt-6 border-t border-[var(--my-border)] pt-6">
            <h2 class="mb-4 font-bold text-[var(--my-neutral)]">Undian</h2>
            <button
              type="button"
              :disabled="raffling || stats.checked_in === 0"
              class="my-btn-secondary w-full border-purple-300 text-purple-600 hover:bg-purple-50 disabled:cursor-not-allowed disabled:opacity-50"
              @click="drawRaffle"
            >
              {{ raffling ? 'Mengundi...' : 'Undi Pemenang' }}
            </button>
            <p v-if="stats.checked_in === 0" class="mt-2 text-xs text-[var(--my-muted)]">
              Belum ada tamu yang check-in.
            </p>
          </div>
        </article>

        <article class="rounded-xl border border-[var(--my-border)] bg-white/60 p-6 lg:col-span-2">
          <h2 class="mb-4 font-bold text-[var(--my-neutral)]">Riwayat Aktivitas</h2>

          <div v-if="entries.length > 0" class="space-y-3">
            <div
              v-for="entry in entries"
              :key="entry.id"
              class="flex items-center justify-between gap-4 rounded-lg border border-[var(--my-border)] bg-white/55 p-4"
            >
              <div class="flex items-center gap-3">
                <span
                  class="rounded-full border px-2.5 py-0.5 text-xs font-semibold"
                  :class="eventTypeBadges[entry.event_type]"
                >
                  {{ eventTypeLabels[entry.event_type] }}
                </span>
                <p class="font-semibold text-[var(--my-neutral)]">{{ entry.guest_name }}</p>
              </div>
              <div class="flex items-center gap-3">
                <span class="text-xs text-[var(--my-muted)]">{{ formatDate(entry.created_at) }}</span>
                <button
                  v-if="entry.event_type === 'check_in'"
                  type="button"
                  class="rounded-lg border border-amber-200 px-3 py-1 text-xs font-semibold text-amber-600 hover:bg-amber-50"
                  :disabled="souvenirForm.processing"
                  @click="submitSouvenir(entry)"
                >
                  Ambil Souvenir
                </button>
              </div>
            </div>
          </div>
          <p v-else class="rounded-lg bg-[var(--my-surface-soft)] px-4 py-8 text-center text-[var(--my-muted)]">
            Belum ada aktivitas buku tamu.
          </p>
        </article>
      </section>
    </div>
  </DashboardLayout>
</template>
