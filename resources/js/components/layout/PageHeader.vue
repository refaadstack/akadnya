<script setup lang="ts">
defineProps<{
    title: string;
    description?: string | null;
}>();
</script>

<template>
    <div
        class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between"
    >
        <div>
            <h1 class="my-heading text-4xl">{{ title }}</h1>
            <p v-if="description" class="my-copy mt-3">
                {{ description }}
            </p>
        </div>
        <div v-if="$slots.actions" class="flex shrink-0 gap-3">
            <slot name="actions" />
        </div>
    </div>
    <div v-if="$slots.extra" class="mb-8">
        <slot name="extra" />
    </div>
</template>
