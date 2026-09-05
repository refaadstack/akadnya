<script setup lang="ts">
defineProps<{
    placeholder?: string;
    required?: boolean;
    disabled?: boolean;
    rows?: number;
}>();

const model = defineModel<string | null>();
</script>

<template>
    <textarea
        v-model="model"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        :rows="rows ?? 3"
        class="w-full rounded-lg border border-gray-300 px-4 py-2 transition focus:border-transparent focus:ring-2 focus:ring-[#AD7F35] disabled:cursor-not-allowed disabled:opacity-50"
    />
</template>
