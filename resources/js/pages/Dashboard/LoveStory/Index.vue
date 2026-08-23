<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';

interface Story {
    id: number | null;
    title: string;
    date_label: string;
    description: string;
    sort_order: number;
}

const props = defineProps<{
    love_story: string;
    special_message: string;
    stories: Story[];
}>();

const form = useForm({
    love_story: props.love_story,
    special_message: props.special_message,
    stories: props.stories.map((story, index) => ({
        ...story,
        sort_order: index,
    })),
});

const addStory = () => {
    form.stories.push({
        id: null,
        title: '',
        date_label: '',
        description: '',
        sort_order: form.stories.length,
    });
};

const removeStory = (index: number) => {
    form.stories.splice(index, 1);
    reorder();
};

const moveStory = (index: number, direction: -1 | 1) => {
    const target = index + direction;

    if (target < 0 || target >= form.stories.length) {
        return;
    }

    const [story] = form.stories.splice(index, 1);
    form.stories.splice(target, 0, story);
    reorder();
};

const reorder = () => {
    form.stories.forEach((story, index) => {
        story.sort_order = index;
    });
};

const submit = () => {
    form.post('/dashboard/love-story', {
        preserveScroll: true,
    });
};
</script>

<template>
    <DashboardLayout>
        <Head title="Love Story" />

        <div class="container mx-auto px-4 py-8">
            <div class="mx-auto max-w-4xl">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Love Story</h1>
                    <p class="mt-2 text-gray-600">
                        Susun kisah cinta kalian dalam timeline dan tulis pesan
                        khusus untuk tamu undangan
                    </p>
                </div>

                <!-- Flash Messages -->
                <div
                    v-if="$page.props.flash?.success"
                    class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800"
                >
                    {{ $page.props.flash.success }}
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Story Timeline -->
                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <div class="mb-2 flex items-center justify-between">
                            <label class="text-lg font-semibold text-gray-900">
                                Timeline Kisah Kami
                            </label>
                            <button
                                type="button"
                                @click="addStory"
                                class="inline-flex items-center rounded-lg bg-green-600 px-3 py-1.5 text-sm text-white transition hover:bg-green-700"
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
                                        d="M12 4v16m8-8H4"
                                    />
                                </svg>
                                Tambah Momen
                            </button>
                        </div>
                        <p class="mb-4 text-sm text-gray-600">
                            Setiap momen tampil sebagai titik dalam timeline
                            undangan. Urutkan dengan tombol naik/turun.
                        </p>

                        <div
                            v-if="form.stories.length === 0"
                            class="rounded-lg border-2 border-dashed border-gray-200 p-8 text-center text-gray-500"
                        >
                            Belum ada momen. Klik "Tambah Momen" untuk mulai
                            menceritakan kisah kalian.
                        </div>

                        <div
                            v-for="(story, index) in form.stories"
                            :key="story.id ?? `new-${index}`"
                            class="mb-4 rounded-lg border border-gray-200 p-4"
                        >
                            <div class="flex items-start gap-3">
                                <div class="flex flex-col">
                                    <button
                                        type="button"
                                        @click="moveStory(index, -1)"
                                        :disabled="index === 0"
                                        class="p-1 text-gray-500 hover:text-green-600 disabled:cursor-not-allowed disabled:opacity-30"
                                        :title="'Naikkan ke atas'"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M5 15l7-7 7 7"
                                            />
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        @click="moveStory(index, 1)"
                                        :disabled="
                                            index === form.stories.length - 1
                                        "
                                        class="p-1 text-gray-500 hover:text-green-600 disabled:cursor-not-allowed disabled:opacity-30"
                                        :title="'Turunkan ke bawah'"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 9l-7 7-7-7"
                                            />
                                        </svg>
                                    </button>
                                </div>

                                <div class="flex-1 space-y-3">
                                    <div
                                        class="grid grid-cols-1 gap-3 sm:grid-cols-3"
                                    >
                                        <div class="sm:col-span-2">
                                            <label
                                                class="mb-1 block text-xs font-medium text-gray-600"
                                                >Judul Momen *</label
                                            >
                                            <input
                                                v-model="story.title"
                                                type="text"
                                                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                                placeholder="Contoh: Pertama Bertemu"
                                            />
                                        </div>
                                        <div>
                                            <label
                                                class="mb-1 block text-xs font-medium text-gray-600"
                                                >Tanggal / Waktu</label
                                            >
                                            <input
                                                v-model="story.date_label"
                                                type="text"
                                                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                                placeholder="Contoh: Januari 2020"
                                            />
                                        </div>
                                    </div>
                                    <div>
                                        <label
                                            class="mb-1 block text-xs font-medium text-gray-600"
                                            >Cerita</label
                                        >
                                        <textarea
                                            v-model="story.description"
                                            rows="3"
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                            placeholder="Ceritakan momen ini..."
                                        ></textarea>
                                    </div>
                                    <div class="flex justify-end">
                                        <button
                                            type="button"
                                            @click="removeStory(index)"
                                            class="inline-flex items-center text-sm text-red-600 hover:text-red-700"
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
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                />
                                            </svg>
                                            Hapus Momen
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Love Story Text -->
                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <label
                            class="mb-2 block text-lg font-semibold text-gray-900"
                        >
                            Kisah Cinta Kami
                        </label>
                        <p class="mb-4 text-sm text-gray-600">
                            Paragraf pengantar sebelum timeline. Kisah ini akan
                            ditampilkan di halaman undangan.
                        </p>
                        <textarea
                            v-model="form.love_story"
                            rows="5"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-green-500"
                            placeholder="Tuliskan kisah cinta kalian di sini..."
                        />
                        <p
                            v-if="form.errors.love_story"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.love_story }}
                        </p>
                    </div>

                    <!-- Special Message -->
                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <label
                            class="mb-2 block text-lg font-semibold text-gray-900"
                        >
                            Pesan Khusus untuk Tamu
                        </label>
                        <p class="mb-4 text-sm text-gray-600">
                            Sampaikan pesan khusus dan ucapan terima kasih untuk
                            tamu yang akan hadir.
                        </p>
                        <textarea
                            v-model="form.special_message"
                            rows="4"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-green-500"
                            placeholder="Tuliskan pesan khusus untuk tamu undangan..."
                        />
                        <p
                            v-if="form.errors.special_message"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.special_message }}
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-green-600 px-6 py-3 font-semibold text-white transition hover:bg-green-700 disabled:opacity-50"
                        >
                            {{
                                form.processing
                                    ? 'Menyimpan...'
                                    : 'Simpan Love Story'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>
