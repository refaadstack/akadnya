<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import Cover from './sections/Cover.vue'
import Opening from './sections/Opening.vue'
import BrideGroom from './sections/BrideGroom.vue'
import EventDetails from './sections/EventDetails.vue'
import LoveStory from './sections/LoveStory.vue'
import Gallery from './sections/Gallery.vue'
import Gift from './sections/Gift.vue'
import Rsvp from './sections/Rsvp.vue'
import Wishes from './sections/Wishes.vue'
import Closing from './sections/Closing.vue'

// Ornaments
import BatikCorner from './ornaments/BatikCorner.vue'
import WayangDivider from './ornaments/WayangDivider.vue'
import GamelanFooter from './ornaments/GamelanFooter.vue'

interface InvitationContent {
  bride_name: string
  bride_father?: string
  bride_mother?: string
  groom_name: string
  groom_father?: string
  groom_mother?: string
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

const props = defineProps<{
  invitation: {
    id: number
    subdomain: string
    content: InvitationContent
    sections: Section[]
    ornaments: Ornament[]
    gallery: GalleryPhoto[]
  }
  guestName?: string | null
}>()

const isPlaying = ref(false)
const audioRef = ref<HTMLAudioElement | null>(null)

const sectionComponents: Record<string, any> = {
  'cover.vue': Cover,
  'opening.vue': Opening,
  'bride-groom.vue': BrideGroom,
  'event-details.vue': EventDetails,
  'love-story.vue': LoveStory,
  'gallery.vue': Gallery,
  'gift.vue': Gift,
  'rsvp.vue': Rsvp,
  'wishes.vue': Wishes,
  'closing.vue': Closing,
}

const ornamentComponents: Record<string, any> = {
  'batik-corner.vue': BatikCorner,
  'wayang-divider.vue': WayangDivider,
  'gamelan-footer.vue': GamelanFooter,
}

const sortedSections = computed(() => {
  return [...props.invitation.sections].sort((a, b) => a.sort_order - b.sort_order)
})

const activeOrnaments = computed(() => {
  return props.invitation.ornaments || []
})

const toggleMusic = () => {
  if (!audioRef.value) return
  
  if (isPlaying.value) {
    audioRef.value.pause()
  } else {
    audioRef.value.play()
  }
  isPlaying.value = !isPlaying.value
}

onMounted(() => {
  // Auto-play music if available
  if (props.invitation.content.music_url && audioRef.value) {
    audioRef.value.play().then(() => {
      isPlaying.value = true
    }).catch(() => {
      // Auto-play blocked, user needs to interact
      isPlaying.value = false
    })
  }
})
</script>

<template>
  <div class="relative bg-amber-50 min-h-screen">
    <!-- Background Music -->
    <audio
      v-if="invitation.content.music_url"
      ref="audioRef"
      :src="invitation.content.music_url"
      loop
    ></audio>

    <!-- Music Control -->
    <button
      v-if="invitation.content.music_url"
      @click="toggleMusic"
      class="fixed bottom-6 right-6 z-50 bg-amber-800 text-white p-4 rounded-full shadow-lg hover:bg-amber-900 transition"
    >
      <svg v-if="isPlaying" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
      </svg>
      <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" clip-rule="evenodd" />
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
      </svg>
    </button>

    <!-- Ornaments - Top -->
    <component
      v-for="ornament in activeOrnaments.filter(o => o.position.includes('top'))"
      :key="ornament.id"
      :is="ornamentComponents[ornament.file]"
      :position="ornament.position"
    />

    <!-- Sections -->
    <component
      v-for="section in sortedSections"
      :key="section.id"
      :is="sectionComponents[section.file]"
      :content="invitation.content"
      :gallery="invitation.gallery"
      :guest-name="guestName"
      :invitation-id="invitation.id"
      :subdomain="invitation.subdomain"
    />

    <!-- Ornaments - Bottom -->
    <component
      v-for="ornament in activeOrnaments.filter(o => o.position.includes('bottom'))"
      :key="ornament.id"
      :is="ornamentComponents[ornament.file]"
      :position="ornament.position"
    />
  </div>
</template>
