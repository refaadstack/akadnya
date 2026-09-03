<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';

interface Rsvp {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    attendance: string;
    guests_count: number;
    message: string | null;
    is_hidden: boolean;
    created_at: string;
    guest?: {
        name: string;
        phone: string | null;
        category: string;
    };
}

interface Stats {
    total: number;
    hadir: number;
    tidak_hadir: number;
}

defineProps<{
    rsvps: {
        data: Rsvp[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    stats: Stats;
}>();

const formatDate = (dateString: string) => {
    const date = new Date(dateString);

    return date.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getAttendanceBadge = (attendance: string) => {
    if (attendance === 'yes') {
        return {
            class: 'bg-[#AD7F35]/10 text-[#5A1B24] border-[#AD7F35]/30',
            label: 'Hadir',
        };
    }

    return {
        class: 'bg-red-100 text-red-800 border-red-200',
        label: 'Tidak Hadir',
    };
};

const toggleWishVisibility = (rsvp: Rsvp) => {
    const hide = !rsvp.is_hidden;
    const message = hide
        ? `Sembunyikan ucapan dari ${rsvp.name} dari undangan publik?`
        : `Tampilkan kembali ucapan dari ${rsvp.name} di undangan?`;

    if (confirm(message)) {
        router.post(`/dashboard/rsvp/${rsvp.id}/${hide ? 'hide' : 'show'}`);
    }
};
</script>

<template>
    <DashboardLayout>
        <Head title="RSVP & Konfirmasi" />

        <div class="py-8">
            <div class="container mx-auto px-4">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">
                        RSVP & Konfirmasi
                    </h1>
                    <p class="mt-1 text-gray-600">
                        Kelola konfirmasi kehadiran tamu undangan Anda
                    </p>
                </div>

                <!-- Stats Cards -->
                <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div
                        class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">
                                    Total RSVP
                                </p>
                                <p
                                    class="mt-2 text-3xl font-bold text-gray-900"
                                >
                                    {{ stats.total }}
                                </p>
                            </div>
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-lg bg-[#5A1B24]/10"
                            >
                                <svg
                                    class="h-6 w-6 text-[#5A1B24]"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div
                        class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">
                                    Konfirmasi Hadir
                                </p>
                                <p
                                    class="mt-2 text-3xl font-bold text-[#AD7F35]"
                                >
                                    {{ stats.hadir }}
                                </p>
                            </div>
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-lg bg-[#AD7F35]/10"
                            >
                                <svg
                                    class="h-6 w-6 text-[#AD7F35]"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div
                        class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">
                                    Tidak Hadir
                                </p>
                                <p class="mt-2 text-3xl font-bold text-red-600">
                                    {{ stats.tidak_hadir }}
                                </p>
                            </div>
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-lg bg-red-100"
                            >
                                <svg
                                    class="h-6 w-6 text-red-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RSVP List -->
                <div
                    class="rounded-xl border border-gray-200 bg-white shadow-sm"
                >
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-900">
                            Daftar RSVP
                        </h2>
                    </div>

                    <div
                        v-if="rsvps.data.length === 0"
                        class="p-12 text-center"
                    >
                        <svg
                            class="mx-auto mb-4 h-16 w-16 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                            />
                        </svg>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900">
                            Belum ada RSVP
                        </h3>
                        <p class="text-gray-600">
                            Tamu yang mengisi form RSVP akan muncul di sini
                        </p>
                    </div>

                    <div v-else class="divide-y divide-gray-200">
                        <div
                            v-for="rsvp in rsvps.data"
                            :key="rsvp.id"
                            class="p-6 transition hover:bg-gray-50"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="mb-2 flex items-center gap-3">
                                        <h3
                                            class="text-lg font-semibold text-gray-900"
                                        >
                                            {{ rsvp.name }}
                                        </h3>
                                        <span
                                            class="rounded-full border px-3 py-1 text-xs font-medium"
                                            :class="
                                                getAttendanceBadge(
                                                    rsvp.attendance,
                                                ).class
                                            "
                                        >
                                            {{
                                                getAttendanceBadge(
                                                    rsvp.attendance,
                                                ).label
                                            }}
                                        </span>
                                    </div>

                                    <div
                                        class="mb-3 grid grid-cols-1 gap-4 text-sm text-gray-600 md:grid-cols-2"
                                    >
                                        <div
                                            v-if="rsvp.email"
                                            class="flex items-center gap-2"
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
                                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                                />
                                            </svg>
                                            {{ rsvp.email }}
                                        </div>
                                        <div
                                            v-if="rsvp.phone"
                                            class="flex items-center gap-2"
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
                                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                                                />
                                            </svg>
                                            {{ rsvp.phone }}
                                        </div>
                                        <div class="flex items-center gap-2">
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
                                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                                />
                                            </svg>
                                            <span
                                                >{{
                                                    rsvp.guests_count
                                                }}
                                                tamu</span
                                            >
                                        </div>
                                        <div class="flex items-center gap-2">
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
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                            </svg>
                                            {{ formatDate(rsvp.created_at) }}
                                        </div>
                                    </div>

                                    <div
                                        v-if="rsvp.message"
                                        class="mt-3 rounded-lg p-3 transition"
                                        :class="
                                            rsvp.is_hidden
                                                ? 'bg-yellow-50 ring-1 ring-yellow-200'
                                                : 'bg-gray-50'
                                        "
                                    >
                                        <p
                                            class="text-sm text-gray-700 italic"
                                            :class="{
                                                'opacity-60': rsvp.is_hidden,
                                            }"
                                        >
                                            "{{ rsvp.message }}"
                                        </p>
                                        <div
                                            class="mt-2 flex items-center gap-3"
                                        >
                                            <span
                                                v-if="rsvp.is_hidden"
                                                class="rounded-full border border-yellow-200 bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800"
                                            >
                                                Disembunyikan dari undangan
                                            </span>
                                            <button
                                                type="button"
                                                class="text-xs font-medium hover:underline"
                                                :class="
                                                    rsvp.is_hidden
                                                        ? 'text-[#AD7F35] hover:text-[#AD7F35]'
                                                        : 'text-red-600 hover:text-red-700'
                                                "
                                                @click="
                                                    toggleWishVisibility(rsvp)
                                                "
                                            >
                                                {{
                                                    rsvp.is_hidden
                                                        ? 'Tampilkan di undangan'
                                                        : 'Sembunyikan dari undangan'
                                                }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="rsvps.last_page > 1"
                        class="border-t border-gray-200 px-6 py-4"
                    >
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-600">
                                Menampilkan
                                {{
                                    (rsvps.current_page - 1) * rsvps.per_page +
                                    1
                                }}
                                -
                                {{
                                    Math.min(
                                        rsvps.current_page * rsvps.per_page,
                                        rsvps.total,
                                    )
                                }}
                                dari {{ rsvps.total }} RSVP
                            </div>
                            <div class="flex gap-2">
                                <Link
                                    v-for="page in rsvps.last_page"
                                    :key="page"
                                    :href="`/dashboard/rsvp?page=${page}`"
                                    class="rounded px-3 py-1"
                                    :class="
                                        page === rsvps.current_page
                                            ? 'bg-[#AD7F35] text-white'
                                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                    "
                                >
                                    {{ page }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
