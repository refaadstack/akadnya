<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { CalendarCheck, Eye, Gift, MessageCircle, Music, Send } from 'lucide-vue-next'
import { computed } from 'vue'
import PublicFooter from '@/components/PublicFooter.vue'
import PublicNavbar from '@/components/PublicNavbar.vue'

interface BasePackage {
  name: string
  price: number
  description: string
}

const props = defineProps<{
  canRegister: boolean
  basePackage: BasePackage | null
}>()

const isLocal = import.meta.env.DEV

const formattedPrice = computed(() => {
  if (!props.basePackage) {
    return 'Rp 0'
  }

  return `Rp ${props.basePackage.price.toLocaleString('id-ID')}`
})

const templates = [
  {
    name: 'Minang Songket Gadang',
    slug: 'minang-songket-gadang',
    tone: 'Tradisional, hangat, dan megah',
    story: 'Untuk pasangan yang ingin membawa kehangatan adat Minangkabau ke setiap tamu undangan.',
    image: 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=900&q=80',
  },
  {
    name: 'Bugis Royal Mappacci',
    slug: 'bugis-royal-mappacci',
    tone: 'Editorial, royal, dan romantis',
    story: 'Nuansa keagungan Bugis yang elegan untuk acara pernikahan yang terasa penuh martabat.',
    image: 'https://images.unsplash.com/photo-1523438885200-e635ba2c371e?auto=format&fit=crop&w=900&q=80',
  },
  {
    name: 'Chinese Imperial Luxe',
    slug: 'chinese-imperial-luxe',
    tone: 'Merah emas, formal, dan mewah',
    story: 'Merah emas yang kaya makna untuk momen bahagia yang dirayakan dengan penuh kebanggaan.',
    image: 'https://images.unsplash.com/photo-1529634899331-b52ed4ee8d7b?auto=format&fit=crop&w=900&q=80',
  },
]

const features = [
  { icon: Eye, title: 'Lihat dulu, baru putuskan', text: 'Kamu bisa cek tampilan asli setiap template sebelum checkout. Yang kamu lihat adalah yang tamu akan buka.' },
  { icon: CalendarCheck, title: 'Kustomisasi sebebasmu', text: 'Isi nama, foto, detail acara, daftar tamu, RSVP, hingga amplop digital dengan santai setelah checkout.' },
  { icon: Music, title: 'Detail yang terasa hidup', text: 'Musik pembuka, galeri foto, hitung mundur hari-H, dan amplop digital sudah ada, tinggal kamu isi.' },
  { icon: Send, title: 'Satu link untuk semua tamu', text: 'Setelah publish, link personal undanganmu siap dibagikan lewat WhatsApp, Instagram, atau media sosial.' },
]

const steps = [
  'Temukan template yang punya jiwa yang sama dengan acaramu.',
  'Preview tampilan aslinya dulu sebelum memutuskan.',
  'Checkout, lalu isi nama, foto, detail acara, dan tamu dengan santai.',
  'Publish, salin link, dan bagikan ke seluruh tamu lewat WhatsApp.',
]
</script>

<template>
  <div class="my-page">
    <Head title="MyAkad - Undangan Digital Pernikahan" />

    <PublicNavbar :can-register="canRegister" current-page="home" />

    <main>
      <section class="min-h-[860px] px-0 pt-24 md:pt-20">
        <div class="my-container grid min-h-[760px] items-center gap-12 py-16 lg:grid-cols-2">
          <div>
            <p class="my-label mb-5">Undangan Digital Premium</p>
            <h1 class="my-heading max-w-2xl text-5xl leading-[0.98] md:text-6xl">
              Abadikan Momen
              <br />
              <span class="my-heading-accent">Terindah Kamu</span>
            </h1>
            <p class="my-copy mt-6 max-w-xl">
              Pilih template dengan karakter budaya yang paling kamu suka, isi detail acara, lalu bagikan linknya ke semua tamu. Siap dalam hitungan menit, tanpa perlu skill desain.
            </p>
            <div class="mt-9 flex flex-wrap gap-4">
              <Link href="/templates" class="my-btn-primary px-9">
                Pilih Template Undanganku
              </Link>
              <a href="#how-it-works" class="my-btn-secondary px-9">
                Cara Pesan
              </a>
            </div>
            <p class="mt-5 text-sm font-semibold text-[var(--my-muted)]">Dipercaya pasangan yang ingin undangan digital terasa personal, rapi, dan mudah dibagikan.</p>
          </div>

          <div class="flex justify-center lg:justify-end">
            <div class="relative w-full max-w-[470px]">
              <img
                class="aspect-square w-full rounded-[96px_40px_96px_40px] object-cover shadow-[0_28px_70px_rgb(51_51_51_/_16%)]"
                src="https://images.unsplash.com/photo-1523438885200-e635ba2c371e?auto=format&fit=crop&w=1000&q=85"
                alt="Detail undangan pernikahan dengan bunga putih dan dedaunan sage"
              />
              <div class="absolute -bottom-5 left-8 rounded-xl border border-[var(--my-border)] bg-white/84 px-5 py-4 shadow-lg backdrop-blur-md">
                <p class="my-label text-[0.66rem]">Preview Asli</p>
                <p class="font-display mt-1 text-xl font-semibold text-[var(--my-neutral)]">Lihat dulu sebelum checkout</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section id="templates" class="bg-white/40 py-20">
        <div class="my-container">
          <div class="mb-12 flex flex-col justify-between gap-5 md:flex-row md:items-end">
            <div>
              <p class="my-label mb-3">Koleksi Nusantara & Global</p>
              <h2 class="my-heading text-4xl">Template yang punya karakter sendiri</h2>
            </div>
            <Link href="/templates" class="my-btn-secondary w-fit px-7">Lihat semua</Link>
          </div>

          <div class="grid gap-6 md:grid-cols-3">
            <article v-for="template in templates" :key="template.slug" class="my-card group overflow-hidden p-3">
              <div class="overflow-hidden rounded-lg">
                <img
                  :src="template.image"
                  :alt="template.name"
                  class="aspect-[3/4] w-full object-cover transition duration-700 group-hover:scale-105"
                />
              </div>
              <div class="p-4">
                <h3 class="my-heading text-2xl">{{ template.name }}</h3>
                <p class="mt-2 text-sm text-[var(--my-muted)]">{{ template.tone }}</p>
                <p class="mt-3 text-sm leading-6 text-[var(--my-neutral)]">{{ template.story }}</p>
                <a
                  :href="`/templates/${template.slug}/render`"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="my-btn-secondary mt-5 w-full"
                >
                  Preview Template
                </a>
              </div>
            </article>
          </div>
        </div>
      </section>

      <section id="how-it-works" class="py-20">
        <div class="my-container grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
          <div>
            <p class="my-label mb-3">Cara Pesan</p>
            <h2 class="my-heading text-4xl">Alurnya sederhana, hasilnya tetap premium.</h2>
            <p class="my-copy mt-5">
              Di MyAkad, kamu bisa preview tampilan asli setiap template sebelum memutuskan beli. Setelah itu tinggal isi data, publish, dan bagikan link undanganmu.
            </p>
          </div>

          <div class="grid gap-4">
            <div v-for="(step, index) in steps" :key="step" class="my-card flex gap-5 p-5">
              <span class="font-display flex size-11 shrink-0 items-center justify-center rounded-full bg-[var(--my-primary)] text-xl font-bold text-white">
                {{ index + 1 }}
              </span>
              <p class="text-lg leading-7 text-[var(--my-neutral)]">{{ step }}</p>
            </div>
          </div>
        </div>
      </section>

      <section id="features" class="bg-[var(--my-surface-soft)]/70 py-20">
        <div class="my-container">
          <div class="mb-12 text-center">
            <p class="my-label mb-3">Fitur Utama</p>
            <h2 class="my-heading text-4xl">Semua kebutuhan undangan digital</h2>
          </div>
          <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
            <article v-for="feature in features" :key="feature.title" class="my-card p-6">
              <component :is="feature.icon" class="mb-5 size-9 text-[var(--my-primary)]" />
              <h3 class="text-xl font-bold text-[var(--my-neutral)]">{{ feature.title }}</h3>
              <p class="mt-3 leading-6 text-[var(--my-muted)]">{{ feature.text }}</p>
            </article>
          </div>
        </div>
      </section>

      <section id="pricing" class="py-20">
        <div class="my-container">
          <div class="mx-auto max-w-2xl text-center">
            <p class="my-label mb-3">Harga</p>
            <h2 class="my-heading text-4xl">Mulai dari {{ basePackage ? `${formattedPrice} / bulan` : 'paket aktif' }}</h2>
            <p class="my-copy mt-4">
              Setara harga beberapa lembar undangan cetak, tapi bisa dibagikan ke banyak tamu sekaligus dan diperbarui kapanpun ada perubahan info.
            </p>
          </div>

          <div class="my-card mx-auto mt-10 max-w-md p-7">
            <div class="text-center">
              <h3 class="my-heading text-3xl">{{ basePackage?.name ?? 'Paket MyAkad' }}</h3>
              <p class="mt-3 text-4xl font-bold text-[var(--my-primary)]">{{ basePackage ? `${formattedPrice} / bulan` : 'Tersedia di checkout' }}</p>
              <p class="mt-3 text-[var(--my-muted)]">{{ basePackage?.description ?? 'Pilih template dulu untuk melihat paket yang tersedia.' }}</p>
            </div>
            <ul class="mt-7 grid gap-3 text-[var(--my-muted)]">
              <li class="flex gap-3"><Gift class="mt-0.5 size-5 text-[var(--my-primary)]" /> Template premium siap pakai</li>
              <li class="flex gap-3"><CalendarCheck class="mt-0.5 size-5 text-[var(--my-primary)]" /> RSVP, galeri, dan data acara</li>
              <li class="flex gap-3"><MessageCircle class="mt-0.5 size-5 text-[var(--my-primary)]" /> Link undangan siap dibagikan</li>
            </ul>
            <Link href="/templates" class="my-btn-primary mt-8 w-full">Pilih Template Undanganku</Link>
          </div>
        </div>
      </section>
    </main>

    <PublicFooter />

    <Link
      v-if="isLocal"
      href="/dev/payment-simulator"
      class="fixed right-6 bottom-6 z-50 rounded-full bg-[var(--my-neutral)] px-4 py-3 text-sm font-bold text-white shadow-2xl transition hover:bg-[var(--my-primary)]"
      title="Payment Simulator (Dev Tool)"
    >
      Payment Simulator
    </Link>
  </div>
</template>
