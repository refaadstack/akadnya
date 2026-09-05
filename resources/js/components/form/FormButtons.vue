<script setup lang="ts">
defineProps<{
    processing?: boolean;
    submitLabel?: string;
    cancelLabel?: string;
    showCancel?: boolean;
}>();

defineEmits<{
    cancel: [];
}>();
</script>

<template>
    <div class="flex gap-3 pt-4">
        <button
            v-if="showCancel ?? true"
            type="button"
            class="flex-1 rounded-lg border-2 border-gray-300 px-6 py-3 font-semibold text-gray-700 transition hover:border-gray-400"
            @click="$emit('cancel')"
        >
            {{ cancelLabel ?? 'Batal' }}
        </button>
        <button
            type="submit"
            :disabled="processing"
            class="flex-1 rounded-lg bg-[#AD7F35] px-6 py-3 font-semibold text-white transition hover:bg-[#5A1B24] disabled:opacity-50"
        >
            {{ processing ? 'Menyimpan...' : (submitLabel ?? 'Simpan') }}
        </button>
    </div>
</template>
