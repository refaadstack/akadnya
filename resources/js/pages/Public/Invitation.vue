<script setup lang="ts">
import { defineAsyncComponent, computed } from 'vue'
import { Head } from '@inertiajs/vue3'

interface InvitationContent {
  bride_name: string
  bride_father?: string
  bride_mother?: string
  bride_instagram?: string
  bride_photo_url?: string
  groom_name: string
  groom_father?: string
  groom_mother?: string
  groom_instagram?: string
  groom_photo_url?: string
  akad_datetime: string
  akad_venue: string
  akad_maps_url?: string
  reception_datetime?: string
  reception_venue?: string
  reception_maps_url?: string
  cover_photo_url?: string
  music_url?: string
  love_story?: string
  special_message?: string
  bank_name?: string
  account_number?: string
  account_name?: string
  qris_image_url?: string
  gopay_number?: string
  ovo_number?: string
  dana_number?: string
}

interface Section {
  id: number
  file: string
  label: string
  sort_order: number
}

interface Ornament {
  id: number
  file: string
  label: string
  position: string
}

interface GalleryPhoto {
  id: number
  image_url: string
  caption?: string
}

interface SEO {
  title: string
  description: string
  url: string
  image: string
  type: string
  bride_name: string
  groom_name: string
  event_date?: string
  guest_name?: string
}

const props = defineProps<{
  invitation: {
    id: number
    subdomain: string
    template: {
      name: string
      slug: string
    }
    content: InvitationContent
    sections: Section[]
    ornaments: Ornament[]
    gallery: GalleryPhoto[]
  }
  guestName?: string | null
  seo: SEO
}>()

// Dynamically load template component based on slug
const TemplateComponent = computed(() => {
  const slug = props.invitation.template.slug
  
  // Map template slug to component path
  // These are pre-built Vue components, not uploaded files
  const templateMap: Record<string, any> = {
    'adat-jawa': defineAsyncComponent(() => import('../../templates/adat-jawa/Index.vue')),
    // 'adat-minang': defineAsyncComponent(() => import('../../templates/adat-minang/Index.vue')),
    // 'adat-bali': defineAsyncComponent(() => import('../../templates/adat-bali/Index.vue')),
  }
  
  return templateMap[slug] || null
})
</script>

<template>
  <div>
    <!-- SEO Meta Tags -->
    <Head>
      <title>{{ seo.title }}</title>
      <meta name="description" :content="seo.description" />
      
      <!-- Open Graph / Facebook -->
      <meta property="og:type" :content="seo.type" />
      <meta property="og:url" :content="seo.url" />
      <meta property="og:title" :content="seo.title" />
      <meta property="og:description" :content="seo.description" />
      <meta property="og:image" :content="seo.image" />
      
      <!-- Twitter -->
      <meta property="twitter:card" content="summary_large_image" />
      <meta property="twitter:url" :content="seo.url" />
      <meta property="twitter:title" :content="seo.title" />
      <meta property="twitter:description" :content="seo.description" />
      <meta property="twitter:image" :content="seo.image" />
      
      <!-- Additional Meta -->
      <meta name="robots" content="index, follow" />
      <link rel="canonical" :href="seo.url" />
    </Head>

    <!-- Render Template Component -->
    <component
      v-if="TemplateComponent"
      :is="TemplateComponent"
      :invitation="invitation"
      :guest-name="guestName"
    />
    
    <!-- Fallback if template not found -->
    <div v-else class="min-h-screen flex items-center justify-center bg-gray-100">
      <div class="text-center">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">Template Not Found</h1>
        <p class="text-gray-600">
          Template "{{ invitation.template.slug }}" is not available yet.
        </p>
      </div>
    </div>
  </div>
</template>
