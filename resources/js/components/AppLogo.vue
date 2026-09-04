<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-md"
    >
        <img
            src="/web-app-manifest-192x192.png"
            alt="Akadnya.com"
            class="size-8"
        />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span
            class="font-display mb-0.5 truncate text-base leading-tight font-bold text-[var(--my-primary)]"
            >Akadnya<span class="text-[var(--my-secondary)]">.com</span></span
        >
    </div>
</template>
