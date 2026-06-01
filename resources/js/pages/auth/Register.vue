<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ArrowLeft, CheckCircle2, LockKeyhole, Mail, UserRound } from 'lucide-vue-next'
import PublicNavbar from '@/components/PublicNavbar.vue'

defineOptions({
  layout: undefined,
})

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  terms: false,
})

const submit = () => {
  form.post('/register', {
    onFinish: () => {
      form.reset('password', 'password_confirmation')
    },
  })
}
</script>

<template>
  <div class="my-page">
    <Head title="Daftar" />

    <PublicNavbar can-register />

    <main class="my-container grid min-h-screen items-center gap-10 pt-28 pb-16 lg:grid-cols-[0.95fr_1.05fr]">
      <section class="hidden lg:block">
        <p class="my-label mb-4">Mulai dari Template</p>
        <h1 class="my-heading max-w-xl text-5xl leading-tight">
          Buat akun, pilih template, lalu publish undanganmu.
        </h1>
        <p class="my-copy mt-5 max-w-lg">
          MyAkad dibuat agar proses undangan digital terasa tenang: preview dulu, isi data pelan-pelan, lalu bagikan satu link saat sudah siap.
        </p>

        <div class="mt-8 grid max-w-md gap-4">
          <div class="my-card flex gap-4 p-5">
            <CheckCircle2 class="mt-1 size-5 shrink-0 text-[var(--my-primary)]" />
            <p class="text-[var(--my-muted)]">Template premium bisa langsung dipakai setelah checkout.</p>
          </div>
          <div class="my-card flex gap-4 p-5">
            <CheckCircle2 class="mt-1 size-5 shrink-0 text-[var(--my-primary)]" />
            <p class="text-[var(--my-muted)]">Editor, RSVP, galeri, dan amplop digital tersedia di dashboard.</p>
          </div>
        </div>
      </section>

      <section class="mx-auto w-full max-w-md">
        <div class="my-card p-7 md:p-8">
          <div class="mb-8 text-center">
            <Link href="/" class="font-display text-4xl font-bold text-[var(--my-primary)]">MyAkad</Link>
            <h2 class="my-heading mt-6 text-3xl">Buat akun baru</h2>
            <p class="mt-2 text-[var(--my-muted)]">Mulai kelola undangan digital pertamamu.</p>
          </div>

          <form class="grid gap-5" @submit.prevent="submit">
            <div>
              <label for="name" class="mb-2 block text-sm font-bold text-[var(--my-neutral)]">Nama Lengkap</label>
              <div class="relative">
                <UserRound class="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-[var(--my-muted)]" />
                <input
                  id="name"
                  v-model="form.name"
                  type="text"
                  required
                  autofocus
                  autocomplete="name"
                  class="my-input min-h-12 pl-11 pr-4"
                  :class="{ 'border-red-500': form.errors.name }"
                  placeholder="Nama kamu"
                />
              </div>
              <p v-if="form.errors.name" class="mt-2 text-sm font-semibold text-red-600">{{ form.errors.name }}</p>
            </div>

            <div>
              <label for="email" class="mb-2 block text-sm font-bold text-[var(--my-neutral)]">Email</label>
              <div class="relative">
                <Mail class="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-[var(--my-muted)]" />
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  required
                  autocomplete="username"
                  class="my-input min-h-12 pl-11 pr-4"
                  :class="{ 'border-red-500': form.errors.email }"
                  placeholder="nama@email.com"
                />
              </div>
              <p v-if="form.errors.email" class="mt-2 text-sm font-semibold text-red-600">{{ form.errors.email }}</p>
            </div>

            <div>
              <label for="password" class="mb-2 block text-sm font-bold text-[var(--my-neutral)]">Password</label>
              <div class="relative">
                <LockKeyhole class="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-[var(--my-muted)]" />
                <input
                  id="password"
                  v-model="form.password"
                  type="password"
                  required
                  autocomplete="new-password"
                  class="my-input min-h-12 pl-11 pr-4"
                  :class="{ 'border-red-500': form.errors.password }"
                  placeholder="Minimal 8 karakter"
                />
              </div>
              <p v-if="form.errors.password" class="mt-2 text-sm font-semibold text-red-600">{{ form.errors.password }}</p>
            </div>

            <div>
              <label for="password_confirmation" class="mb-2 block text-sm font-bold text-[var(--my-neutral)]">Konfirmasi Password</label>
              <div class="relative">
                <LockKeyhole class="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-[var(--my-muted)]" />
                <input
                  id="password_confirmation"
                  v-model="form.password_confirmation"
                  type="password"
                  required
                  autocomplete="new-password"
                  class="my-input min-h-12 pl-11 pr-4"
                  :class="{ 'border-red-500': form.errors.password_confirmation }"
                  placeholder="Ulangi password"
                />
              </div>
              <p v-if="form.errors.password_confirmation" class="mt-2 text-sm font-semibold text-red-600">{{ form.errors.password_confirmation }}</p>
            </div>

            <div>
              <label class="flex items-start gap-3 text-sm text-[var(--my-muted)]">
                <input v-model="form.terms" type="checkbox" required class="mt-1 size-4 rounded border-[var(--my-border)] text-[var(--my-primary)] focus:ring-[var(--my-primary)]" />
                <span>Saya setuju dengan syarat layanan dan kebijakan privasi MyAkad.</span>
              </label>
              <p v-if="form.errors.terms" class="mt-2 text-sm font-semibold text-red-600">{{ form.errors.terms }}</p>
            </div>

            <button type="submit" :disabled="form.processing" class="my-btn-primary w-full disabled:cursor-not-allowed disabled:opacity-60">
              {{ form.processing ? 'Memproses...' : 'Daftar Sekarang' }}
            </button>
          </form>

          <p class="mt-6 text-center text-[var(--my-muted)]">
            Sudah punya akun?
            <Link href="/login" class="font-bold text-[var(--my-primary)]">Masuk</Link>
          </p>
        </div>

        <div class="mt-6 text-center">
          <Link href="/" class="inline-flex items-center gap-2 text-sm font-bold text-[var(--my-muted)] transition hover:text-[var(--my-primary)]">
            <ArrowLeft class="size-4" />
            Kembali ke beranda
          </Link>
        </div>
      </section>
    </main>
  </div>
</template>
