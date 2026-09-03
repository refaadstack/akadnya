<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineOptions({ layout: undefined });

const props = defineProps<{
    invitation?: any;
    error?: string;
}>();
</script>

<template>
    <div>
        <Head title="Preview Undangan" />

        <div class="min-h-screen bg-gray-900">
            <!-- Top Bar -->
            <div class="border-b border-gray-700 bg-gray-800">
                <div
                    class="container mx-auto flex items-center justify-between px-4 py-3"
                >
                    <div class="flex items-center space-x-4">
                        <Link
                            href="/dashboard/editor"
                            class="text-gray-300 transition hover:text-white"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </Link>
                        <div>
                            <h1 class="font-semibold text-white">
                                Preview Mode
                            </h1>
                            <p class="text-xs text-gray-400">
                                Template:
                                {{ invitation?.template?.name || 'N/A' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3">
                        <span
                            v-if="invitation?.status === 'published'"
                            class="rounded-full bg-[#AD7F35] px-3 py-1 text-xs font-semibold text-white"
                        >
                            Published
                        </span>
                        <span
                            v-else
                            class="rounded-full bg-gray-600 px-3 py-1 text-xs font-semibold text-white"
                        >
                            Draft
                        </span>

                        <Link
                            href="/dashboard/editor"
                            class="rounded-lg bg-[#AD7F35] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#9f6b61]"
                        >
                            Kembali ke Editor
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Error State -->
            <div v-if="error" class="container mx-auto px-4 py-16 text-center">
                <svg
                    class="mx-auto mb-4 h-24 w-24 text-gray-600"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                    />
                </svg>
                <h2 class="mb-2 text-2xl font-bold text-white">
                    Konten Belum Tersedia
                </h2>
                <p class="mb-6 text-gray-400">{{ error }}</p>
                <Link
                    href="/dashboard/editor"
                    class="inline-block rounded-lg bg-[#AD7F35] px-6 py-3 font-semibold text-white transition hover:bg-[#9f6b61]"
                >
                    Isi Konten Sekarang
                </Link>
            </div>

            <!-- Preview Content -->
            <div v-else class="h-[calc(100vh-64px)]">
                <!-- Placeholder for actual template rendering -->
                <div
                    class="h-full overflow-auto bg-gradient-to-br from-[#AD7F35]/10 via-white to-[#D8BA82]/10"
                >
                    <div class="container mx-auto px-4 py-16">
                        <!-- Cover Section -->
                        <div
                            class="relative mb-16 flex h-screen items-center justify-center"
                        >
                            <div
                                v-if="invitation.content.cover_photo_url"
                                class="absolute inset-0 bg-cover bg-center"
                                :style="{
                                    backgroundImage: `url(${invitation.content.cover_photo_url})`,
                                }"
                            >
                                <div
                                    class="bg-opacity-40 absolute inset-0 bg-black"
                                ></div>
                            </div>
                            <div
                                v-else
                                class="absolute inset-0 bg-gradient-to-br from-[#AD7F35] to-[#D8BA82]"
                            ></div>

                            <div
                                class="relative z-10 px-4 text-center text-white"
                            >
                                <p class="mb-2 text-lg">The Wedding of</p>
                                <h1 class="mb-4 text-5xl font-bold md:text-7xl">
                                    {{ invitation.content.bride_name }} &
                                    {{ invitation.content.groom_name }}
                                </h1>
                                <p
                                    v-if="invitation.content.akad_datetime"
                                    class="text-xl md:text-2xl"
                                >
                                    {{
                                        new Date(
                                            invitation.content.akad_datetime,
                                        ).toLocaleDateString('id-ID', {
                                            weekday: 'long',
                                            year: 'numeric',
                                            month: 'long',
                                            day: 'numeric',
                                        })
                                    }}
                                </p>
                            </div>
                        </div>

                        <!-- Mempelai Section -->
                        <div class="mx-auto mb-16 max-w-4xl">
                            <h2
                                class="mb-12 text-center text-3xl font-bold text-gray-900"
                            >
                                Mempelai
                            </h2>
                            <div class="grid gap-8 md:grid-cols-2">
                                <!-- Bride -->
                                <div class="text-center">
                                    <div
                                        class="mx-auto mb-4 h-48 w-48 rounded-full bg-gradient-to-br from-[#AD7F35]/20 to-[#AD7F35]/20"
                                    ></div>
                                    <h3
                                        class="mb-2 text-2xl font-bold text-gray-900"
                                    >
                                        {{ invitation.content.bride_name }}
                                    </h3>
                                    <p
                                        v-if="
                                            invitation.content.bride_father ||
                                            invitation.content.bride_mother
                                        "
                                        class="text-gray-600"
                                    >
                                        Putri dari<br />
                                        <span
                                            v-if="
                                                invitation.content.bride_father
                                            "
                                            >{{
                                                invitation.content.bride_father
                                            }}</span
                                        >
                                        <span
                                            v-if="
                                                invitation.content
                                                    .bride_father &&
                                                invitation.content.bride_mother
                                            "
                                        >
                                            &
                                        </span>
                                        <span
                                            v-if="
                                                invitation.content.bride_mother
                                            "
                                            >{{
                                                invitation.content.bride_mother
                                            }}</span
                                        >
                                    </p>
                                </div>

                                <!-- Groom -->
                                <div class="text-center">
                                    <div
                                        class="mx-auto mb-4 h-48 w-48 rounded-full bg-gradient-to-br from-[#5A1B24]/20 to-[#AD7F35]/20"
                                    ></div>
                                    <h3
                                        class="mb-2 text-2xl font-bold text-gray-900"
                                    >
                                        {{ invitation.content.groom_name }}
                                    </h3>
                                    <p
                                        v-if="
                                            invitation.content.groom_father ||
                                            invitation.content.groom_mother
                                        "
                                        class="text-gray-600"
                                    >
                                        Putra dari<br />
                                        <span
                                            v-if="
                                                invitation.content.groom_father
                                            "
                                            >{{
                                                invitation.content.groom_father
                                            }}</span
                                        >
                                        <span
                                            v-if="
                                                invitation.content
                                                    .groom_father &&
                                                invitation.content.groom_mother
                                            "
                                        >
                                            &
                                        </span>
                                        <span
                                            v-if="
                                                invitation.content.groom_mother
                                            "
                                            >{{
                                                invitation.content.groom_mother
                                            }}</span
                                        >
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Event Details -->
                        <div class="mx-auto mb-16 max-w-4xl">
                            <h2
                                class="mb-12 text-center text-3xl font-bold text-gray-900"
                            >
                                Detail Acara
                            </h2>
                            <div class="grid gap-8 md:grid-cols-2">
                                <!-- Akad -->
                                <div class="rounded-xl bg-white p-8 shadow-lg">
                                    <h3
                                        class="mb-4 text-2xl font-bold text-[#AD7F35]"
                                    >
                                        Akad Nikah
                                    </h3>
                                    <div class="space-y-3 text-gray-700">
                                        <div class="flex items-start">
                                            <svg
                                                class="mt-0.5 mr-2 h-5 w-5 text-[#AD7F35]"
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
                                                <p class="font-semibold">
                                                    {{
                                                        new Date(
                                                            invitation.content
                                                                .akad_datetime,
                                                        ).toLocaleDateString(
                                                            'id-ID',
                                                            {
                                                                weekday: 'long',
                                                                year: 'numeric',
                                                                month: 'long',
                                                                day: 'numeric',
                                                            },
                                                        )
                                                    }}
                                                </p>
                                                <p>
                                                    {{
                                                        new Date(
                                                            invitation.content
                                                                .akad_datetime,
                                                        ).toLocaleTimeString(
                                                            'id-ID',
                                                            {
                                                                hour: '2-digit',
                                                                minute: '2-digit',
                                                            },
                                                        )
                                                    }}
                                                    WIB
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-start">
                                            <svg
                                                class="mt-0.5 mr-2 h-5 w-5 text-[#AD7F35]"
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
                                            <p>
                                                {{
                                                    invitation.content
                                                        .akad_venue
                                                }}
                                            </p>
                                        </div>
                                        <a
                                            v-if="
                                                invitation.content.akad_maps_url
                                            "
                                            :href="
                                                invitation.content.akad_maps_url
                                            "
                                            target="_blank"
                                            class="mt-2 inline-block rounded-lg bg-[#AD7F35] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#9f6b61]"
                                        >
                                            Lihat Lokasi
                                        </a>
                                    </div>
                                </div>

                                <!-- Reception -->
                                <div
                                    v-if="invitation.content.reception_datetime"
                                    class="rounded-xl bg-white p-8 shadow-lg"
                                >
                                    <h3
                                        class="mb-4 text-2xl font-bold text-[#AD7F35]"
                                    >
                                        Resepsi
                                    </h3>
                                    <div class="space-y-3 text-gray-700">
                                        <div class="flex items-start">
                                            <svg
                                                class="mt-0.5 mr-2 h-5 w-5 text-[#AD7F35]"
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
                                                <p class="font-semibold">
                                                    {{
                                                        new Date(
                                                            invitation.content
                                                                .reception_datetime,
                                                        ).toLocaleDateString(
                                                            'id-ID',
                                                            {
                                                                weekday: 'long',
                                                                year: 'numeric',
                                                                month: 'long',
                                                                day: 'numeric',
                                                            },
                                                        )
                                                    }}
                                                </p>
                                                <p>
                                                    {{
                                                        new Date(
                                                            invitation.content
                                                                .reception_datetime,
                                                        ).toLocaleTimeString(
                                                            'id-ID',
                                                            {
                                                                hour: '2-digit',
                                                                minute: '2-digit',
                                                            },
                                                        )
                                                    }}
                                                    WIB
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-start">
                                            <svg
                                                class="mt-0.5 mr-2 h-5 w-5 text-[#AD7F35]"
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
                                            <p>
                                                {{
                                                    invitation.content
                                                        .reception_venue
                                                }}
                                            </p>
                                        </div>
                                        <a
                                            v-if="
                                                invitation.content
                                                    .reception_maps_url
                                            "
                                            :href="
                                                invitation.content
                                                    .reception_maps_url
                                            "
                                            target="_blank"
                                            class="mt-2 inline-block rounded-lg bg-[#AD7F35] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#9f6b61]"
                                        >
                                            Lihat Lokasi
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Love Story -->
                        <div
                            v-if="invitation.content.love_story"
                            class="mx-auto mb-16 max-w-3xl"
                        >
                            <h2
                                class="mb-8 text-center text-3xl font-bold text-gray-900"
                            >
                                Cerita Cinta Kami
                            </h2>
                            <div class="rounded-xl bg-white p-8 shadow-lg">
                                <p
                                    class="leading-relaxed whitespace-pre-line text-gray-700"
                                >
                                    {{ invitation.content.love_story }}
                                </p>
                            </div>
                        </div>

                        <!-- Special Message -->
                        <div
                            v-if="invitation.content.special_message"
                            class="mx-auto mb-16 max-w-3xl"
                        >
                            <div
                                class="rounded-xl bg-gradient-to-r from-[#AD7F35]/10 to-[#D8BA82]/20 p-8 text-center"
                            >
                                <p class="text-lg text-gray-800 italic">
                                    {{ invitation.content.special_message }}
                                </p>
                            </div>
                        </div>

                        <!-- Preview Note -->
                        <div class="mx-auto max-w-3xl text-center">
                            <div
                                class="rounded-lg border border-[#5A1B24]/30 bg-[#5A1B24]/10 p-6"
                            >
                                <svg
                                    class="mx-auto mb-3 h-12 w-12 text-[#5A1B24]"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                                <h3
                                    class="mb-2 text-lg font-semibold text-[#5A1B24]"
                                >
                                    Mode Preview
                                </h3>
                                <p class="text-sm text-[#5A1B24]">
                                    Ini adalah preview undangan Anda. Template
                                    lengkap dengan semua sections dan ornaments
                                    akan ditampilkan setelah dipublikasikan.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
