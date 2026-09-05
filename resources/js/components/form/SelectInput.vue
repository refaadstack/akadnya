<script setup lang="ts">
defineProps<{
    options: Array<{ value: string | number; label: string }>;
    required?: boolean;
    disabled?: boolean;
}>();

const model = defineModel<string | number | null>();
</script>

<template>
    <select
        v-model="model"
        :required="required"
        :disabled="disabled"
        class="w-full rounded-lg border border-gray-300 px-4 py-2 transition focus:border-transparent focus:ring-2 focus:ring-[#AD7F35] disabled:cursor-not-allowed disabled:opacity-50"
    >
        <option
            v-for="option in options"
            :key="option.value"
            :value="option.value"
        >
            {{ option.label }}
        </option>
    </select>
</template>
