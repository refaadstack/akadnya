<script setup lang="ts">
defineProps<{
    content: any;
}>();

const formatDate = (datetime: string) => {
    return new Date(datetime).toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatTime = (datetime: string) => {
    return (
        new Date(datetime).toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
        }) + ' WIB'
    );
};
</script>

<template>
    <section class="bg-gradient-to-b from-white to-amber-50 px-4 py-20">
        <div class="container mx-auto max-w-6xl">
            <!-- Title -->
            <div class="mb-16 text-center">
                <h2
                    class="mb-4 font-serif text-4xl font-bold text-amber-800 md:text-5xl"
                >
                    Detail Acara
                </h2>
                <div class="flex items-center justify-center">
                    <div class="h-px w-20 bg-amber-400"></div>
                    <svg
                        class="mx-4 h-8 w-8 text-amber-600"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    <div class="h-px w-20 bg-amber-400"></div>
                </div>
            </div>

            <!-- Events Grid -->
            <div class="grid gap-8 md:grid-cols-2">
                <!-- Akad Nikah -->
                <div
                    class="overflow-hidden rounded-2xl border-4 border-amber-200 bg-white shadow-xl"
                >
                    <!-- Header -->
                    <div
                        class="bg-gradient-to-r from-amber-600 to-amber-700 p-6 text-center text-white"
                    >
                        <svg
                            class="mx-auto mb-3 h-16 w-16"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"
                            />
                        </svg>
                        <h3 class="font-serif text-2xl font-bold">
                            Akad Nikah
                        </h3>
                    </div>

                    <!-- Content -->
                    <div class="space-y-4 p-8">
                        <!-- Date -->
                        <div class="flex items-start">
                            <svg
                                class="mt-1 mr-3 h-6 w-6 shrink-0 text-amber-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                            <div>
                                <p class="font-semibold text-gray-900">
                                    {{ formatDate(content.akad_datetime) }}
                                </p>
                                <p class="font-semibold text-amber-700">
                                    {{ formatTime(content.akad_datetime) }}
                                </p>
                            </div>
                        </div>

                        <!-- Venue -->
                        <div class="flex items-start">
                            <svg
                                class="mt-1 mr-3 h-6 w-6 shrink-0 text-amber-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                            </svg>
                            <div>
                                <p class="font-semibold text-gray-900">
                                    {{ content.akad_venue }}
                                </p>
                            </div>
                        </div>

                        <!-- Maps Button -->
                        <a
                            v-if="content.akad_maps_url"
                            :href="content.akad_maps_url"
                            target="_blank"
                            class="mt-6 block w-full rounded-lg bg-amber-600 py-3 text-center font-semibold text-white transition hover:bg-amber-700"
                        >
                            <svg
                                class="mr-2 inline-block h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"
                                />
                            </svg>
                            Lihat Lokasi
                        </a>
                    </div>
                </div>

                <!-- Resepsi -->
                <div
                    v-if="content.reception_datetime"
                    class="overflow-hidden rounded-2xl border-4 border-amber-200 bg-white shadow-xl"
                >
                    <!-- Header -->
                    <div
                        class="bg-gradient-to-r from-amber-700 to-amber-800 p-6 text-center text-white"
                    >
                        <svg
                            class="mx-auto mb-3 h-16 w-16"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                d="M2 6a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 100 4v2a2 2 0 01-2 2H4a2 2 0 01-2-2v-2a2 2 0 100-4V6z"
                            />
                        </svg>
                        <h3 class="font-serif text-2xl font-bold">Resepsi</h3>
                    </div>

                    <!-- Content -->
                    <div class="space-y-4 p-8">
                        <!-- Date -->
                        <div class="flex items-start">
                            <svg
                                class="mt-1 mr-3 h-6 w-6 shrink-0 text-amber-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                            <div>
                                <p class="font-semibold text-gray-900">
                                    {{ formatDate(content.reception_datetime) }}
                                </p>
                                <p class="font-semibold text-amber-700">
                                    {{ formatTime(content.reception_datetime) }}
                                </p>
                            </div>
                        </div>

                        <!-- Venue -->
                        <div class="flex items-start">
                            <svg
                                class="mt-1 mr-3 h-6 w-6 shrink-0 text-amber-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                            </svg>
                            <div>
                                <p class="font-semibold text-gray-900">
                                    {{ content.reception_venue }}
                                </p>
                            </div>
                        </div>

                        <!-- Maps Button -->
                        <a
                            v-if="content.reception_maps_url"
                            :href="content.reception_maps_url"
                            target="_blank"
                            class="mt-6 block w-full rounded-lg bg-amber-600 py-3 text-center font-semibold text-white transition hover:bg-amber-700"
                        >
                            <svg
                                class="mr-2 inline-block h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"
                                />
                            </svg>
                            Lihat Lokasi
                        </a>
                    </div>
                </div>
            </div>

            <!-- Save the Date -->
            <div class="mt-12 text-center">
                <div
                    class="inline-block rounded-xl border-2 border-amber-400 bg-amber-100 p-6"
                >
                    <p class="mb-2 text-lg text-amber-900">Simpan Tanggal</p>
                    <p class="font-serif text-3xl font-bold text-amber-800">
                        {{
                            new Date(content.akad_datetime).toLocaleDateString(
                                'id-ID',
                                {
                                    day: 'numeric',
                                    month: 'long',
                                    year: 'numeric',
                                },
                            )
                        }}
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>
