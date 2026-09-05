<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ChevronDown, ChevronUp } from 'lucide-vue-next';
import { ref } from 'vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';

defineOptions({
    layout: undefined,
});

interface Section {
    id: number;
    section_key: string;
    name: string;
    is_visible: boolean;
    sort_order: number;
}

interface Ornament {
    id: number;
    ornament_key: string;
    name: string;
    position: string;
    is_active: boolean;
}

const props = defineProps<{
    invitation: {
        id: number;
        status: string;
        template: {
            name: string;
            slug: string;
        };
    };
    sections: Section[];
    ornaments: Ornament[];
}>();

const sections = ref([...props.sections]);
const ornaments = ref([...props.ornaments]);
const draggedSection = ref<number | null>(null);
const saveStatus = ref<'idle' | 'saving' | 'saved' | 'error'>('idle');

let saveStatusTimer: ReturnType<typeof setTimeout> | null = null;

const flashSaveStatus = (status: 'saved' | 'error') => {
    saveStatus.value = status;

    if (saveStatusTimer) {
        clearTimeout(saveStatusTimer);
    }

    saveStatusTimer = setTimeout(() => {
        saveStatus.value = 'idle';
    }, 3000);
};

// Drag and drop handlers
const handleDragStart = (index: number) => {
    draggedSection.value = index;
};

const handleDragOver = (event: DragEvent) => {
    event.preventDefault();
};

const handleDrop = (index: number) => {
    if (draggedSection.value === null) {
        return;
    }

    const items = [...sections.value];
    const draggedItem = items[draggedSection.value];
    items.splice(draggedSection.value, 1);
    items.splice(index, 0, draggedItem);

    sections.value = items;
    draggedSection.value = null;

    // Save new order
    saveOrder();
};

const saveOrder = async () => {
    const sectionIds = sections.value.map((s) => s.id);
    const xsrfToken = decodeURIComponent(
        document.cookie
            .split('; ')
            .find((row) => row.startsWith('XSRF-TOKEN='))
            ?.split('=')[1] ?? '',
    );

    saveStatus.value = 'saving';

    try {
        const response = await fetch('/dashboard/sections/reorder', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-XSRF-TOKEN': xsrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ section_ids: sectionIds }),
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        flashSaveStatus('saved');
    } catch (error) {
        console.error('Failed to save order:', error);
        flashSaveStatus('error');
    }
};

const moveSection = (index: number, direction: -1 | 1) => {
    const target = index + direction;

    if (target < 0 || target >= sections.value.length) {
        return;
    }

    const items = [...sections.value];
    const [moved] = items.splice(index, 1);
    items.splice(target, 0, moved);

    sections.value = items;
    saveOrder();
};

const toggleSection = async (sectionId: number) => {
    const xsrfToken = decodeURIComponent(
        document.cookie
            .split('; ')
            .find((row) => row.startsWith('XSRF-TOKEN='))
            ?.split('=')[1] ?? '',
    );

    try {
        const response = await fetch(
            `/dashboard/sections/${sectionId}/toggle`,
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-XSRF-TOKEN': xsrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            },
        );

        const data = await response.json();

        if (data.success) {
            const section = sections.value.find((s) => s.id === sectionId);

            if (section) {
                section.is_visible = data.is_visible;
            }
        } else {
            alert(data.message || 'Gagal toggle section');
        }
    } catch (error) {
        console.error('Failed to toggle section:', error);
        alert('Gagal toggle section');
    }
};

const toggleOrnament = async (ornamentId: number) => {
    const xsrfToken = decodeURIComponent(
        document.cookie
            .split('; ')
            .find((row) => row.startsWith('XSRF-TOKEN='))
            ?.split('=')[1] ?? '',
    );

    try {
        const response = await fetch(
            `/dashboard/ornaments/${ornamentId}/toggle`,
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-XSRF-TOKEN': xsrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            },
        );

        const data = await response.json();

        if (data.success) {
            const ornament = ornaments.value.find((o) => o.id === ornamentId);

            if (ornament) {
                ornament.is_active = data.is_active;
            }
        }
    } catch (error) {
        console.error('Failed to toggle ornament:', error);
        alert('Gagal toggle ornament');
    }
};

const getPositionLabel = (position: string) => {
    const labels: Record<string, string> = {
        top: 'Atas',
        bottom: 'Bawah',
        between: 'Antar Section',
        overlay: 'Overlay',
    };

    return labels[position] || position;
};
</script>

<template>
    <DashboardLayout>
        <Head title="Kustomisasi Undangan" />

        <div class="min-h-screen bg-gray-50">
            <!-- Main Content -->
            <div class="container mx-auto px-4 py-8">
                <!-- Header -->
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <Link
                            href="/dashboard"
                            class="mb-2 inline-flex items-center text-sm text-gray-600 hover:text-[#AD7F35]"
                        >
                            <svg
                                class="mr-1 h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 19l-7-7 7-7"
                                />
                            </svg>
                            Kembali ke Dashboard
                        </Link>
                        <h1 class="text-3xl font-bold text-gray-900">
                            Kustomisasi Undangan
                        </h1>
                        <p class="mt-1 text-gray-600">
                            Template: {{ invitation.template.name }}
                        </p>
                    </div>
                </div>

                <div class="grid gap-8 lg:grid-cols-2">
                    <!-- Sections Management -->
                    <div class="rounded-xl bg-white p-6 shadow-md">
                        <div class="mb-6 flex items-center justify-between">
                            <h2 class="text-xl font-bold text-gray-900">
                                Kelola Section
                            </h2>
                            <div class="text-sm text-gray-600">
                                <svg
                                    class="mr-1 inline h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"
                                    />
                                </svg>
                                Drag atau tombol panah untuk reorder
                                <span
                                    v-if="saveStatus === 'saving'"
                                    class="ml-2 font-semibold text-gray-500"
                                >
                                    Menyimpan…
                                </span>
                                <span
                                    v-else-if="saveStatus === 'saved'"
                                    class="ml-2 font-semibold text-green-700"
                                >
                                    Urutan tersimpan
                                </span>
                                <span
                                    v-else-if="saveStatus === 'error'"
                                    class="ml-2 font-semibold text-red-600"
                                >
                                    Gagal menyimpan, coba lagi
                                </span>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="(section, index) in sections"
                                :key="section.id"
                                draggable="true"
                                @dragstart="handleDragStart(index)"
                                @dragover="handleDragOver"
                                @drop="handleDrop(index)"
                                class="flex cursor-move items-center justify-between rounded-lg border-2 border-gray-200 p-4 transition hover:border-[#AD7F35]/40"
                                :class="{ 'opacity-50': !section.is_visible }"
                            >
                                <div class="flex items-center space-x-3">
                                    <svg
                                        class="h-5 w-5 text-gray-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 6h16M4 12h16M4 18h16"
                                        />
                                    </svg>
                                    <div>
                                        <p class="font-semibold text-gray-900">
                                            {{ section.name }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ section.section_key }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1">
                                    <button
                                        type="button"
                                        :disabled="index === 0"
                                        class="rounded-lg p-1.5 text-gray-500 transition hover:bg-gray-100 hover:text-gray-800 disabled:cursor-not-allowed disabled:opacity-30"
                                        title="Pindahkan ke atas"
                                        aria-label="Pindahkan section ke atas"
                                        @click="moveSection(index, -1)"
                                    >
                                        <ChevronUp class="size-4" />
                                    </button>
                                    <button
                                        type="button"
                                        :disabled="
                                            index === sections.length - 1
                                        "
                                        class="rounded-lg p-1.5 text-gray-500 transition hover:bg-gray-100 hover:text-gray-800 disabled:cursor-not-allowed disabled:opacity-30"
                                        title="Pindahkan ke bawah"
                                        aria-label="Pindahkan section ke bawah"
                                        @click="moveSection(index, 1)"
                                    >
                                        <ChevronDown class="size-4" />
                                    </button>
                                    <label
                                        class="relative inline-flex cursor-pointer items-center"
                                    >
                                        <input
                                            type="checkbox"
                                            :checked="section.is_visible"
                                            @change="toggleSection(section.id)"
                                            class="peer sr-only"
                                        />
                                        <div
                                            class="peer h-6 w-11 rounded-full bg-gray-200 peer-checked:bg-[#AD7F35] peer-focus:ring-4 peer-focus:ring-[#AD7F35]/40 peer-focus:outline-none after:absolute after:top-[2px] after:left-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full peer-checked:after:border-white"
                                        ></div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div
                            class="mt-6 rounded-lg border border-[#5A1B24]/30 bg-[#5A1B24]/10 p-4"
                        >
                            <div class="flex items-start">
                                <svg
                                    class="mt-0.5 mr-2 h-5 w-5 text-[#5A1B24]"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <div class="text-sm text-[#5A1B24]">
                                    <p class="font-semibold">Tips:</p>
                                    <p class="mt-1">
                                        Section yang wajib (hero, countdown,
                                        rsvp) tidak dapat disembunyikan.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ornaments Management -->
                    <div class="rounded-xl bg-white p-6 shadow-md">
                        <h2 class="mb-6 text-xl font-bold text-gray-900">
                            Kelola Ornamen
                        </h2>

                        <div class="space-y-6">
                            <div
                                v-for="position in [
                                    'top',
                                    'bottom',
                                    'between',
                                    'overlay',
                                ]"
                                :key="position"
                            >
                                <h3
                                    class="mb-3 text-sm font-semibold text-gray-700"
                                >
                                    {{ getPositionLabel(position) }}
                                </h3>

                                <div class="space-y-2">
                                    <div
                                        v-for="ornament in ornaments.filter(
                                            (o) => o.position === position,
                                        )"
                                        :key="ornament.id"
                                        class="flex items-center justify-between rounded-lg border border-gray-200 p-3"
                                    >
                                        <div>
                                            <p
                                                class="font-medium text-gray-900"
                                            >
                                                {{ ornament.name }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ ornament.ornament_key }}
                                            </p>
                                        </div>

                                        <label
                                            class="relative inline-flex cursor-pointer items-center"
                                        >
                                            <input
                                                type="checkbox"
                                                :checked="ornament.is_active"
                                                @change="
                                                    toggleOrnament(ornament.id)
                                                "
                                                class="peer sr-only"
                                            />
                                            <div
                                                class="peer h-6 w-11 rounded-full bg-gray-200 peer-checked:bg-[#AD7F35] peer-focus:ring-4 peer-focus:ring-[#AD7F35]/40 peer-focus:outline-none after:absolute after:top-[2px] after:left-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full peer-checked:after:border-white"
                                            ></div>
                                        </label>
                                    </div>

                                    <p
                                        v-if="
                                            ornaments.filter(
                                                (o) => o.position === position,
                                            ).length === 0
                                        "
                                        class="text-sm text-gray-500 italic"
                                    >
                                        Tidak ada ornamen di posisi ini
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview Button -->
                <div class="mt-8 text-center">
                    <a
                        :href="`/templates/${invitation.template.slug}/render`"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center rounded-lg bg-[#AD7F35] px-8 py-3 font-semibold text-white transition hover:bg-[#5A1B24]"
                    >
                        <svg
                            class="mr-2 h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                            />
                        </svg>
                        Preview Undangan
                    </a>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
