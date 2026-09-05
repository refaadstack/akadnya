<script setup lang="ts">
defineProps<{
    type?: string;
    placeholder?: string;
    required?: boolean;
    disabled?: boolean;
    min?: number | string;
    max?: number | string;
}>();

const model = defineModel<string | number | null>();
</script>

<template>
    <input
        v-model="model"
        :type="type ?? 'text'"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        :min="min"
        :max="max"
        class="w-full rounded-lg border border-gray-300 px-4 py-2 transition focus:border-transparent focus:ring-2 focus:ring-[#AD7F35] disabled:cursor-not-allowed disabled:opacity-50"
    />
</template>
