<script setup lang="ts">
import { useHttp } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

interface Wish {
    id: number;
    name: string;
    message: string;
    attendance: string;
    created_at: string;
}

const props = defineProps<{
    invitationId: number;
}>();

const wishes = ref<Wish[]>([]);
const loading = ref(true);
const http = useHttp<Record<string, never>, { data: Wish[] }>();

const loadWishes = async () => {
    try {
        const response = await http.get(
            `/api/invitations/${props.invitationId}/wishes`,
        );
        wishes.value = response.data;
    } catch (error) {
        console.error('Failed to load wishes:', error);
    } finally {
        loading.value = false;
    }
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);

    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

onMounted(() => {
    loadWishes();
});
</script>

<template>
    <section id="wishes" class="bg-amber-50 px-4 py-20">
        <div class="mx-auto max-w-4xl">
            <!-- Header -->
            <div class="mb-12 text-center">
                <h2 class="mb-4 font-serif text-4xl text-amber-900">
                    Ucapan & Doa
                </h2>
                <div class="mx-auto mb-6 h-1 w-24 bg-amber-600"></div>
                <p class="text-amber-800">
                    Doa dan ucapan dari Anda sangat berarti bagi kami
                </p>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="py-12 text-center">
                <div
                    class="inline-block h-12 w-12 animate-spin rounded-full border-4 border-amber-200 border-t-amber-600"
                ></div>
                <p class="mt-4 text-amber-700">Memuat ucapan...</p>
            </div>

            <!-- Empty State -->
            <div v-else-if="wishes.length === 0" class="py-12 text-center">
                <svg
                    class="mx-auto mb-4 h-24 w-24 text-amber-300"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                    />
                </svg>
                <p class="text-lg text-amber-700">Belum ada ucapan</p>
                <p class="mt-2 text-sm text-amber-600">
                    Jadilah yang pertama memberikan ucapan dan doa
                </p>
            </div>

            <!-- Wishes List -->
            <div v-else class="space-y-6">
                <div
                    v-for="wish in wishes"
                    :key="wish.id"
                    class="rounded-lg border-l-4 border-amber-500 bg-white p-6 shadow-md transition hover:shadow-lg"
                >
                    <!-- Header -->
                    <div class="mb-3 flex items-start justify-between">
                        <div class="flex items-center">
                            <div
                                class="mr-4 flex h-12 w-12 items-center justify-center rounded-full bg-amber-100"
                            >
                                <span class="text-lg font-bold text-amber-800">
                                    {{ wish.name.charAt(0).toUpperCase() }}
                                </span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-amber-900">
                                    {{ wish.name }}
                                </h3>
                                <p class="text-sm text-amber-600">
                                    {{ formatDate(wish.created_at) }}
                                </p>
                            </div>
                        </div>
                        <span
                            v-if="wish.attendance === 'yes'"
                            class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700"
                        >
                            ✓ Hadir
                        </span>
                        <span
                            v-else-if="wish.attendance === 'no'"
                            class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700"
                        >
                            Tidak Hadir
                        </span>
                    </div>

                    <!-- Message -->
                    <p
                        v-if="wish.message"
                        class="pl-16 leading-relaxed text-amber-800"
                    >
                        "{{ wish.message }}"
                    </p>
                    <p v-else class="pl-16 text-amber-500 italic">
                        Tidak ada pesan
                    </p>
                </div>
            </div>

            <!-- Show More Button (if needed in future) -->
            <div v-if="wishes.length >= 10" class="mt-8 text-center">
                <button
                    class="rounded-lg bg-amber-700 px-6 py-3 text-white transition hover:bg-amber-800"
                >
                    Muat Lebih Banyak
                </button>
            </div>
        </div>
    </section>
</template>
