<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import {
    Field,
    FormButtons,
    SelectInput,
    TextArea,
    TextInput,
} from '@/components/form';
import DashboardLayout from '@/layouts/DashboardLayout.vue';

interface Guest {
    id: number;
    name: string;
    phone: string | null;
    category: string;
    unique_code: string;
    max_pax: number;
    notes: string | null;
    personal_link: string;
    has_rsvp: boolean;
    rsvp: {
        attendance: string;
        pax_count: number;
        message: string | null;
    } | null;
}

interface Stats {
    total: number;
    family: number;
    friends: number;
    colleagues: number;
    others: number;
    confirmed: number;
    declined: number;
    pending: number;
}

interface RsvpItem {
    id: number;
    name: string;
    attendance: string;
    pax_count: number;
    message: string | null;
    is_hidden: boolean;
    is_orphan: boolean;
    created_at: string;
    guest: {
        id: number;
        name: string;
        phone: string | null;
        category: string;
    } | null;
}

interface RsvpStats {
    total: number;
    hadir: number;
    tidak_hadir: number;
    tanpa_kode: number;
}

interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{
    invitation: {
        id: number;
        status: string;
    };
    activeTab: string;
    guests: {
        data: Guest[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    stats: Stats;
    rsvps: Paginated<RsvpItem>;
    rsvpStats: RsvpStats;
    filters: {
        search: string | null;
        category: string;
    };
}>();

const activeTab = ref(props.activeTab === 'rsvp' ? 'rsvp' : 'daftar');

watch(
    () => props.activeTab,
    (tab) => {
        activeTab.value = tab === 'rsvp' ? 'rsvp' : 'daftar';
    },
);

const switchTab = (tab: 'daftar' | 'rsvp') => {
    activeTab.value = tab;
};

const isPublished = computed(() => props.invitation?.status === 'published');

const showAddModal = ref(false);
const showEditModal = ref(false);
const showImportModal = ref(false);
const editingGuest = ref<Guest | null>(null);
const searchQuery = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category || 'all');

const addForm = useForm({
    name: '',
    phone: '',
    category: 'family',
    max_pax: 1,
    notes: '',
});

const editForm = useForm({
    name: '',
    phone: '',
    category: 'family',
    max_pax: 1,
    notes: '',
});

const importForm = useForm({
    file: null as File | null,
});

const categoryLabels: Record<string, string> = {
    all: 'Semua',
    family: 'Keluarga',
    friends: 'Teman',
    colleagues: 'Rekan Kerja',
    others: 'Lainnya',
};

const categoryOptions = [
    { value: 'family', label: 'Keluarga' },
    { value: 'friends', label: 'Teman' },
    { value: 'colleagues', label: 'Rekan Kerja' },
    { value: 'others', label: 'Lainnya' },
];

const categoryColors: Record<string, string> = {
    family: 'bg-[#AD7F35]/10 text-[#5A1B24]',
    friends: 'bg-[#5A1B24]/10 text-[#5A1B24]',
    colleagues: 'bg-[#AD7F35]/10 text-[#5A1B24]',
    others: 'bg-gray-100 text-gray-800',
};

const openAddModal = () => {
    addForm.reset();
    showAddModal.value = true;
};

const closeAddModal = () => {
    showAddModal.value = false;
    addForm.reset();
};

const submitAdd = () => {
    addForm.post('/dashboard/guests', {
        onSuccess: () => {
            closeAddModal();
        },
    });
};

const openEditModal = (guest: Guest) => {
    editingGuest.value = guest;
    editForm.name = guest.name;
    editForm.phone = guest.phone || '';
    editForm.category = guest.category;
    editForm.max_pax = guest.max_pax;
    editForm.notes = guest.notes || '';
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    editingGuest.value = null;
    editForm.reset();
};

const submitEdit = () => {
    if (!editingGuest.value) {
        return;
    }

    editForm.put(`/dashboard/guests/${editingGuest.value.id}`, {
        onSuccess: () => {
            closeEditModal();
        },
    });
};

const deleteGuest = (guest: Guest) => {
    if (confirm(`Hapus tamu ${guest.name}?`)) {
        router.delete(`/dashboard/guests/${guest.id}`);
    }
};

const copyLink = (link: string) => {
    if (!isPublished.value) {
        return;
    }

    navigator.clipboard.writeText(link);
    alert('Link berhasil disalin!');
};

const sendWhatsApp = (guestId: number) => {
    if (!isPublished.value) {
        return;
    }

    window.open(`/dashboard/guests/${guestId}/whatsapp`, '_blank');
};

const filterByCategory = (category: string) => {
    selectedCategory.value = category;
    router.get(
        '/dashboard/guests',
        {
            tab: 'daftar',
            category,
            search: searchQuery.value,
        },
        {
            preserveState: true,
        },
    );
};

const search = () => {
    router.get(
        '/dashboard/guests',
        {
            tab: 'daftar',
            category: selectedCategory.value,
            search: searchQuery.value,
        },
        {
            preserveState: true,
        },
    );
};

const openImportModal = () => {
    importForm.reset();
    showImportModal.value = true;
};

const closeImportModal = () => {
    showImportModal.value = false;
    importForm.reset();
};

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;

    if (target.files && target.files[0]) {
        importForm.file = target.files[0];
    }
};

const submitImport = () => {
    importForm.post('/dashboard/guests/import', {
        onSuccess: () => {
            closeImportModal();
        },
    });
};

const exportGuests = () => {
    window.location.href = '/dashboard/guests/export';
};

const downloadTemplate = () => {
    // Create CSV template
    const csv =
        'Nama,Telepon,Kategori,Max Pax,Catatan\nJohn Doe,081234567890,family,2,Catatan opsional';
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'template-import-tamu.csv';
    a.click();
    window.URL.revokeObjectURL(url);
};

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

const toggleWishVisibility = (rsvp: RsvpItem) => {
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
        <Head title="Tamu" />

        <div class="py-8">
            <div class="container mx-auto px-4">
                <!-- Page Header -->
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Tamu</h1>
                        <p class="mt-1 text-gray-600">
                            Daftar tamu, konfirmasi kehadiran, dan ucapan dalam
                            satu tempat
                        </p>
                    </div>
                    <div v-if="activeTab === 'daftar'" class="flex gap-3">
                        <button
                            @click="openImportModal"
                            class="rounded-lg border-2 border-gray-300 px-6 py-3 font-semibold text-gray-700 transition hover:border-gray-400"
                        >
                            📥 Import
                        </button>
                        <button
                            @click="exportGuests"
                            class="rounded-lg border-2 border-gray-300 px-6 py-3 font-semibold text-gray-700 transition hover:border-gray-400"
                        >
                            📤 Export
                        </button>
                        <button
                            @click="openAddModal"
                            class="rounded-lg bg-[#AD7F35] px-6 py-3 font-semibold text-white transition hover:bg-[#5A1B24]"
                        >
                            + Tambah Tamu
                        </button>
                    </div>
                </div>

                <!-- Flash Messages -->
                <div
                    v-if="$page.props.flash?.success"
                    class="mb-6 rounded-lg border border-[#AD7F35]/30 bg-[#AD7F35]/10 px-4 py-3 text-[#5A1B24]"
                >
                    {{ $page.props.flash.success }}
                </div>
                <div
                    v-if="$page.props.flash?.error"
                    class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800"
                >
                    {{ $page.props.flash.error }}
                </div>

                <!-- Unpublished warning -->
                <div
                    v-if="!isPublished"
                    class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800"
                >
                    <p class="font-semibold">Undangan belum dipublikasikan</p>
                    <p class="mt-1 text-sm">
                        Link tamu belum bisa diakses (404). Publikasikan
                        undangan di menu Pengaturan terlebih dahulu, lalu salin
                        link tamu setelahnya.
                    </p>
                </div>

                <!-- Tabs -->
                <div class="mb-6 flex gap-2">
                    <button
                        type="button"
                        @click="switchTab('daftar')"
                        class="rounded-lg px-5 py-2.5 font-semibold transition"
                        :class="
                            activeTab === 'daftar'
                                ? 'bg-[#AD7F35] text-white hover:bg-[#5A1B24]'
                                : 'bg-white text-gray-700 shadow-md hover:bg-gray-100'
                        "
                    >
                        Daftar Tamu ({{ stats.total }})
                    </button>
                    <button
                        type="button"
                        @click="switchTab('rsvp')"
                        class="rounded-lg px-5 py-2.5 font-semibold transition"
                        :class="
                            activeTab === 'rsvp'
                                ? 'bg-[#AD7F35] text-white hover:bg-[#5A1B24]'
                                : 'bg-white text-gray-700 shadow-md hover:bg-gray-100'
                        "
                    >
                        Konfirmasi & Ucapan ({{ rsvpStats.total }})
                    </button>
                </div>

                <div v-if="activeTab === 'daftar'">
                    <!-- Stats Cards -->
                    <div class="mb-8 grid gap-6 md:grid-cols-4">
                        <div class="rounded-xl bg-white p-6 shadow-md">
                            <div class="mb-1 text-3xl font-bold text-gray-900">
                                {{ stats.total }}
                            </div>
                            <div class="text-sm text-gray-600">Total Tamu</div>
                        </div>
                        <div class="rounded-xl bg-white p-6 shadow-md">
                            <div class="mb-1 text-3xl font-bold text-[#AD7F35]">
                                {{ stats.confirmed }}
                            </div>
                            <div class="text-sm text-gray-600">
                                Konfirmasi Hadir
                            </div>
                        </div>
                        <div class="rounded-xl bg-white p-6 shadow-md">
                            <div class="mb-1 text-3xl font-bold text-red-600">
                                {{ stats.declined }}
                            </div>
                            <div class="text-sm text-gray-600">Tidak Hadir</div>
                        </div>
                        <div class="rounded-xl bg-white p-6 shadow-md">
                            <div
                                class="mb-1 text-3xl font-bold text-yellow-600"
                            >
                                {{ stats.pending }}
                            </div>
                            <div class="text-sm text-gray-600">
                                Belum Konfirmasi
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="mb-6 rounded-xl bg-white p-6 shadow-md">
                        <div class="flex flex-wrap items-center gap-4">
                            <!-- Search -->
                            <div class="min-w-[300px] flex-1">
                                <input
                                    v-model="searchQuery"
                                    @keyup.enter="search"
                                    type="text"
                                    placeholder="Cari nama atau telepon..."
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-[#AD7F35]"
                                />
                            </div>

                            <!-- Category Filter -->
                            <div class="flex gap-2">
                                <button
                                    v-for="(label, key) in categoryLabels"
                                    :key="key"
                                    @click="filterByCategory(key)"
                                    class="rounded-lg px-4 py-2 font-medium transition"
                                    :class="
                                        selectedCategory === key
                                            ? 'bg-[#AD7F35] text-white hover:bg-[#5A1B24]'
                                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                    "
                                >
                                    {{ label }}
                                    <span v-if="key !== 'all'" class="ml-1">
                                        ({{ stats[key as keyof Stats] }})
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Guest List -->
                    <div class="overflow-hidden rounded-xl bg-white shadow-md">
                        <div
                            v-if="guests.data.length === 0"
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
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                                />
                            </svg>
                            <h3
                                class="mb-2 text-lg font-semibold text-gray-900"
                            >
                                Belum ada tamu
                            </h3>
                            <p class="mb-4 text-gray-600">
                                Mulai tambahkan tamu undangan Anda
                            </p>
                            <button
                                @click="openAddModal"
                                class="rounded-lg bg-[#AD7F35] px-6 py-3 font-semibold text-white transition hover:bg-[#5A1B24]"
                            >
                                + Tambah Tamu Pertama
                            </button>
                        </div>

                        <table v-else class="w-full">
                            <thead class="border-b border-gray-200 bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        Nama
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        Telepon
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        Kategori
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        Max Pax
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        Status RSVP
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                                    >
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr
                                    v-for="guest in guests.data"
                                    :key="guest.id"
                                    class="hover:bg-gray-50"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900">
                                            {{ guest.name }}
                                        </div>
                                        <div
                                            v-if="guest.notes"
                                            class="text-sm text-gray-500"
                                        >
                                            {{ guest.notes }}
                                        </div>
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm whitespace-nowrap text-gray-900"
                                    >
                                        {{ guest.phone || '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="rounded-full px-2 py-1 text-xs font-medium"
                                            :class="
                                                categoryColors[guest.category]
                                            "
                                        >
                                            {{ categoryLabels[guest.category] }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm whitespace-nowrap text-gray-900"
                                    >
                                        {{ guest.max_pax }} orang
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            v-if="guest.has_rsvp"
                                            class="rounded-full px-2 py-1 text-xs font-medium"
                                            :class="
                                                guest.rsvp?.attendance === 'yes'
                                                    ? 'bg-[#AD7F35]/10 text-[#5A1B24]'
                                                    : 'bg-red-100 text-red-800'
                                            "
                                        >
                                            {{
                                                guest.rsvp?.attendance === 'yes'
                                                    ? `Hadir (${guest.rsvp.pax_count})`
                                                    : 'Tidak Hadir'
                                            }}
                                        </span>
                                        <span
                                            v-else
                                            class="rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-800"
                                        >
                                            Belum Konfirmasi
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm whitespace-nowrap"
                                    >
                                        <div class="flex gap-2">
                                            <button
                                                @click="
                                                    copyLink(
                                                        guest.personal_link,
                                                    )
                                                "
                                                class="text-[#5A1B24] hover:text-[#5A1B24] disabled:cursor-not-allowed disabled:opacity-30"
                                                title="Copy Link"
                                                :disabled="!isPublished"
                                            >
                                                🔗
                                            </button>
                                            <button
                                                v-if="guest.phone"
                                                @click="sendWhatsApp(guest.id)"
                                                class="text-[#AD7F35] hover:text-[#5A1B24] disabled:cursor-not-allowed disabled:opacity-30"
                                                title="Kirim WhatsApp"
                                                :disabled="!isPublished"
                                            >
                                                📱
                                            </button>
                                            <button
                                                @click="openEditModal(guest)"
                                                class="text-gray-600 hover:text-gray-800"
                                                title="Edit"
                                            >
                                                ✏️
                                            </button>
                                            <button
                                                @click="deleteGuest(guest)"
                                                class="text-red-600 hover:text-red-800"
                                                title="Hapus"
                                            >
                                                🗑️
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div
                            v-if="guests.last_page > 1"
                            class="flex items-center justify-between border-t border-gray-200 px-6 py-4"
                        >
                            <div class="text-sm text-gray-700">
                                Menampilkan
                                {{
                                    (guests.current_page - 1) *
                                        guests.per_page +
                                    1
                                }}
                                -
                                {{
                                    Math.min(
                                        guests.current_page * guests.per_page,
                                        guests.total,
                                    )
                                }}
                                dari {{ guests.total }} tamu
                            </div>
                            <div class="flex gap-2">
                                <Link
                                    v-for="page in guests.last_page"
                                    :key="page"
                                    :href="`/dashboard/guests?tab=${activeTab}&page=${page}&category=${selectedCategory}&search=${searchQuery}`"
                                    class="rounded px-3 py-1"
                                    :class="
                                        page === guests.current_page
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

                <div v-else>
                    <!-- RSVP Stats Cards -->
                    <div class="mb-8 grid gap-6 md:grid-cols-4">
                        <div class="rounded-xl bg-white p-6 shadow-md">
                            <div class="mb-1 text-3xl font-bold text-gray-900">
                                {{ rsvpStats.total }}
                            </div>
                            <div class="text-sm text-gray-600">
                                Total Konfirmasi
                            </div>
                        </div>
                        <div class="rounded-xl bg-white p-6 shadow-md">
                            <div class="mb-1 text-3xl font-bold text-[#AD7F35]">
                                {{ rsvpStats.hadir }}
                            </div>
                            <div class="text-sm text-gray-600">
                                Konfirmasi Hadir
                            </div>
                        </div>
                        <div class="rounded-xl bg-white p-6 shadow-md">
                            <div class="mb-1 text-3xl font-bold text-red-600">
                                {{ rsvpStats.tidak_hadir }}
                            </div>
                            <div class="text-sm text-gray-600">Tidak Hadir</div>
                        </div>
                        <div class="rounded-xl bg-white p-6 shadow-md">
                            <div
                                class="mb-1 text-3xl font-bold text-yellow-600"
                            >
                                {{ rsvpStats.tanpa_kode }}
                            </div>
                            <div class="text-sm text-gray-600">
                                Tanpa Kode Tamu
                            </div>
                        </div>
                    </div>

                    <!-- RSVP List -->
                    <div class="overflow-hidden rounded-xl bg-white shadow-md">
                        <div
                            v-if="rsvps.data.length === 0"
                            class="p-12 text-center"
                        >
                            <h3
                                class="mb-2 text-lg font-semibold text-gray-900"
                            >
                                Belum ada konfirmasi
                            </h3>
                            <p class="text-gray-600">
                                Bagikan link personal dari tab Daftar Tamu agar
                                setiap konfirmasi tercatat atas nama tamu yang
                                tepat.
                            </p>
                        </div>

                        <div v-else class="divide-y divide-gray-200">
                            <div
                                v-for="rsvp in rsvps.data"
                                :key="rsvp.id"
                                class="p-6 transition hover:bg-gray-50"
                            >
                                <div
                                    class="mb-2 flex flex-wrap items-center gap-3"
                                >
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        {{ rsvp.name }}
                                    </h3>
                                    <span
                                        class="rounded-full border px-3 py-1 text-xs font-medium"
                                        :class="
                                            getAttendanceBadge(rsvp.attendance)
                                                .class
                                        "
                                    >
                                        {{
                                            getAttendanceBadge(rsvp.attendance)
                                                .label
                                        }}
                                        <span v-if="rsvp.attendance === 'yes'">
                                            ({{ rsvp.pax_count }})
                                        </span>
                                    </span>
                                    <span
                                        v-if="rsvp.is_orphan"
                                        class="rounded-full border border-yellow-200 bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-800"
                                    >
                                        Tanpa kode tamu
                                    </span>
                                    <span
                                        v-else-if="rsvp.guest"
                                        class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700"
                                    >
                                        {{ rsvp.guest.name }}
                                        <span v-if="rsvp.guest.phone">
                                            · {{ rsvp.guest.phone }}
                                        </span>
                                    </span>
                                </div>

                                <p class="text-sm text-gray-500">
                                    {{ formatDate(rsvp.created_at) }}
                                </p>

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
                                    <div class="mt-2 flex items-center gap-3">
                                        <span
                                            v-if="rsvp.is_hidden"
                                            class="rounded-full border border-yellow-200 bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800"
                                        >
                                            Disembunyikan dari undangan
                                        </span>
                                        <button
                                            type="button"
                                            class="rounded-lg p-1.5 transition"
                                            :class="
                                                rsvp.is_hidden
                                                    ? 'text-[#AD7F35] hover:bg-[#AD7F35]/10'
                                                    : 'text-gray-400 hover:bg-gray-100 hover:text-gray-600'
                                            "
                                            :title="
                                                rsvp.is_hidden
                                                    ? 'Tampilkan di undangan'
                                                    : 'Sembunyikan dari undangan'
                                            "
                                            :aria-label="
                                                rsvp.is_hidden
                                                    ? 'Tampilkan di undangan'
                                                    : 'Sembunyikan dari undangan'
                                            "
                                            @click="toggleWishVisibility(rsvp)"
                                        >
                                            <EyeOff
                                                v-if="rsvp.is_hidden"
                                                class="size-4"
                                            />
                                            <Eye v-else class="size-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- RSVP Pagination -->
                        <div
                            v-if="rsvps.last_page > 1"
                            class="flex items-center justify-between border-t border-gray-200 px-6 py-4"
                        >
                            <div class="text-sm text-gray-700">
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
                                dari {{ rsvps.total }} konfirmasi
                            </div>
                            <div class="flex gap-2">
                                <Link
                                    v-for="page in rsvps.last_page"
                                    :key="page"
                                    :href="`/dashboard/guests?tab=rsvp&rsvp_page=${page}`"
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

        <!-- Add Modal -->
        <div
            v-if="showAddModal"
            class="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center bg-black p-4"
        >
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h2 class="mb-6 text-2xl font-bold text-gray-900">
                    Tambah Tamu
                </h2>

                <form @submit.prevent="submitAdd" class="space-y-4">
                    <Field label="Nama" required :error="addForm.errors.name">
                        <TextInput v-model="addForm.name" required />
                    </Field>

                    <Field label="Telepon" :error="addForm.errors.phone">
                        <TextInput
                            v-model="addForm.phone"
                            placeholder="081234567890"
                        />
                    </Field>

                    <Field
                        label="Kategori"
                        required
                        :error="addForm.errors.category"
                    >
                        <SelectInput
                            v-model="addForm.category"
                            :options="categoryOptions"
                            required
                        />
                    </Field>

                    <Field
                        label="Maksimal Tamu"
                        required
                        :error="addForm.errors.max_pax"
                    >
                        <TextInput
                            v-model.number="addForm.max_pax"
                            type="number"
                            min="1"
                            max="10"
                            required
                        />
                    </Field>

                    <Field label="Catatan" :error="addForm.errors.notes">
                        <TextArea v-model="addForm.notes" />
                    </Field>

                    <FormButtons
                        :processing="addForm.processing"
                        @cancel="closeAddModal"
                    />
                </form>
            </div>
        </div>

        <!-- Edit Modal -->
        <div
            v-if="showEditModal"
            class="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center bg-black p-4"
        >
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h2 class="mb-6 text-2xl font-bold text-gray-900">Edit Tamu</h2>

                <form @submit.prevent="submitEdit" class="space-y-4">
                    <Field label="Nama" required :error="editForm.errors.name">
                        <TextInput v-model="editForm.name" required />
                    </Field>

                    <Field label="Telepon" :error="editForm.errors.phone">
                        <TextInput
                            v-model="editForm.phone"
                            placeholder="081234567890"
                        />
                    </Field>

                    <Field
                        label="Kategori"
                        required
                        :error="editForm.errors.category"
                    >
                        <SelectInput
                            v-model="editForm.category"
                            :options="categoryOptions"
                            required
                        />
                    </Field>

                    <Field
                        label="Maksimal Tamu"
                        required
                        :error="editForm.errors.max_pax"
                    >
                        <TextInput
                            v-model.number="editForm.max_pax"
                            type="number"
                            min="1"
                            max="10"
                            required
                        />
                    </Field>

                    <Field label="Catatan" :error="editForm.errors.notes">
                        <TextArea v-model="editForm.notes" />
                    </Field>

                    <FormButtons
                        :processing="editForm.processing"
                        @cancel="closeEditModal"
                    />
                </form>
            </div>
        </div>

        <!-- Import Modal -->
        <div
            v-if="showImportModal"
            class="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center bg-black p-4"
        >
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h2 class="mb-6 text-2xl font-bold text-gray-900">
                    Import Tamu dari CSV
                </h2>

                <div
                    class="mb-4 rounded-lg border border-[#5A1B24]/30 bg-[#5A1B24]/10 p-4"
                >
                    <p class="mb-2 text-sm text-[#5A1B24]">Format CSV:</p>
                    <code class="text-xs text-[#5A1B24]"
                        >Nama,Telepon,Kategori,Max Pax,Catatan</code
                    >
                    <p class="mt-2 text-xs text-[#9f6b61]">
                        Kategori: family, friends, colleagues, others
                    </p>
                </div>

                <button
                    @click="downloadTemplate"
                    class="mb-4 w-full rounded-lg border-2 border-gray-300 px-4 py-2 font-medium text-gray-700 transition hover:bg-gray-50"
                >
                    📥 Download Template CSV
                </button>

                <form @submit.prevent="submitImport" class="space-y-4">
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700"
                            >File CSV *</label
                        >
                        <input
                            type="file"
                            accept=".csv,.txt"
                            required
                            @change="handleFileChange"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-[#AD7F35]"
                        />
                        <div
                            v-if="importForm.errors.file"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ importForm.errors.file }}
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button
                            type="button"
                            @click="closeImportModal"
                            class="flex-1 rounded-lg border-2 border-gray-300 px-6 py-3 font-semibold text-gray-700 transition hover:bg-gray-50"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="importForm.processing"
                            class="flex-1 rounded-lg bg-[#AD7F35] px-6 py-3 font-semibold text-white transition hover:bg-[#5A1B24] disabled:opacity-50"
                        >
                            {{
                                importForm.processing
                                    ? 'Mengimport...'
                                    : 'Import'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>
