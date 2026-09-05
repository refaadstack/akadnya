<script setup lang="ts">
defineProps<{
    label: string;
    error?: string | null;
    hint?: string | null;
    required?: boolean;
    inputId?: string;
}>();
</script>

<template>
    <div>
        <label
            v-if="label"
            :for="inputId"
            class="mb-2 block text-sm font-medium text-gray-700"
        >
            {{ label }}<span v-if="required"> *</span>
        </label>
        <slot />
        <p v-if="hint && !error" class="mt-2 text-xs text-gray-500">
            {{ hint }}
        </p>
        <p v-if="error" class="mt-1 text-sm text-red-600">
            {{ error }}
        </p>
    </div>
</template>
