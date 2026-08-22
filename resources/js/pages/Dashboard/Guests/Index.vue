<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

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

const props = defineProps<{
    invitation: {
        id: number;
        status: string;
    };
    guests: {
        data: Guest[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    stats: Stats;
    filters: {
        search: string | null;
        category: string;
    };
}>();

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

const categoryColors: Record<string, string> = {
    family: 'bg-green-100 text-green-800',
    friends: 'bg-blue-100 text-blue-800',
    colleagues: 'bg-green-100 text-green-800',
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
    if (!editingGuest.value) return;

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
</script>

<template>
    <DashboardLayout>
        <Head title="Manajemen Tamu" />

        <div class="py-8">
            <div class="container mx-auto px-4">
                <!-- Page Header -->
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">
                            Manajemen Tamu
                        </h1>
                        <p class="mt-1 text-gray-600">
                            Kelola daftar tamu undangan Anda
                        </p>
                    </div>
                    <div class="flex gap-3">
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
                            class="rounded-lg bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-3 font-semibold text-white transition hover:shadow-lg"
                        >
                            + Tambah Tamu
                        </button>
                    </div>
                </div>

                <!-- Flash Messages -->
                <div
                    v-if="$page.props.flash?.success"
                    class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800"
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

                <!-- Stats Cards -->
                <div class="mb-8 grid gap-6 md:grid-cols-4">
                    <div class="rounded-xl bg-white p-6 shadow-md">
                        <div class="mb-1 text-3xl font-bold text-gray-900">
                            {{ stats.total }}
                        </div>
                        <div class="text-sm text-gray-600">Total Tamu</div>
                    </div>
                    <div class="rounded-xl bg-white p-6 shadow-md">
                        <div class="mb-1 text-3xl font-bold text-green-600">
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
                        <div class="mb-1 text-3xl font-bold text-yellow-600">
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
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
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
                                        ? 'bg-gradient-to-r from-green-600 to-emerald-600 text-white'
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
                        <h3 class="mb-2 text-lg font-semibold text-gray-900">
                            Belum ada tamu
                        </h3>
                        <p class="mb-4 text-gray-600">
                            Mulai tambahkan tamu undangan Anda
                        </p>
                        <button
                            @click="openAddModal"
                            class="rounded-lg bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-3 font-semibold text-white transition hover:shadow-lg"
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
                                        :class="categoryColors[guest.category]"
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
                                                ? 'bg-green-100 text-green-800'
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
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    <div class="flex gap-2">
                                        <button
                                            @click="
                                                copyLink(guest.personal_link)
                                            "
                                            class="text-blue-600 hover:text-blue-800 disabled:cursor-not-allowed disabled:opacity-30"
                                            title="Copy Link"
                                            :disabled="!isPublished"
                                        >
                                            🔗
                                        </button>
                                        <button
                                            v-if="guest.phone"
                                            @click="sendWhatsApp(guest.id)"
                                            class="text-green-600 hover:text-green-800 disabled:cursor-not-allowed disabled:opacity-30"
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
                                (guests.current_page - 1) * guests.per_page + 1
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
                                :href="`/dashboard/guests?page=${page}&category=${selectedCategory}&search=${searchQuery}`"
                                class="rounded px-3 py-1"
                                :class="
                                    page === guests.current_page
                                        ? 'bg-green-600 text-white'
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
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700"
                            >Nama *</label
                        >
                        <input
                            v-model="addForm.name"
                            type="text"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                        />
                        <div
                            v-if="addForm.errors.name"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ addForm.errors.name }}
                        </div>
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700"
                            >Telepon</label
                        >
                        <input
                            v-model="addForm.phone"
                            type="text"
                            placeholder="081234567890"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                        />
                        <div
                            v-if="addForm.errors.phone"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ addForm.errors.phone }}
                        </div>
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700"
                            >Kategori *</label
                        >
                        <select
                            v-model="addForm.category"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                        >
                            <option value="family">Keluarga</option>
                            <option value="friends">Teman</option>
                            <option value="colleagues">Rekan Kerja</option>
                            <option value="others">Lainnya</option>
                        </select>
                        <div
                            v-if="addForm.errors.category"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ addForm.errors.category }}
                        </div>
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700"
                            >Maksimal Tamu *</label
                        >
                        <input
                            v-model.number="addForm.max_pax"
                            type="number"
                            min="1"
                            max="10"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                        />
                        <div
                            v-if="addForm.errors.max_pax"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ addForm.errors.max_pax }}
                        </div>
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700"
                            >Catatan</label
                        >
                        <textarea
                            v-model="addForm.notes"
                            rows="3"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                        ></textarea>
                        <div
                            v-if="addForm.errors.notes"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ addForm.errors.notes }}
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button
                            type="button"
                            @click="closeAddModal"
                            class="flex-1 rounded-lg border-2 border-gray-300 px-6 py-3 font-semibold text-gray-700 transition hover:bg-gray-50"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="addForm.processing"
                            class="flex-1 rounded-lg bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-3 font-semibold text-white transition hover:shadow-lg disabled:opacity-50"
                        >
                            {{ addForm.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
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
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700"
                            >Nama *</label
                        >
                        <input
                            v-model="editForm.name"
                            type="text"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                        />
                        <div
                            v-if="editForm.errors.name"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ editForm.errors.name }}
                        </div>
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700"
                            >Telepon</label
                        >
                        <input
                            v-model="editForm.phone"
                            type="text"
                            placeholder="081234567890"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                        />
                        <div
                            v-if="editForm.errors.phone"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ editForm.errors.phone }}
                        </div>
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700"
                            >Kategori *</label
                        >
                        <select
                            v-model="editForm.category"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                        >
                            <option value="family">Keluarga</option>
                            <option value="friends">Teman</option>
                            <option value="colleagues">Rekan Kerja</option>
                            <option value="others">Lainnya</option>
                        </select>
                        <div
                            v-if="editForm.errors.category"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ editForm.errors.category }}
                        </div>
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700"
                            >Maksimal Tamu *</label
                        >
                        <input
                            v-model.number="editForm.max_pax"
                            type="number"
                            min="1"
                            max="10"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                        />
                        <div
                            v-if="editForm.errors.max_pax"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ editForm.errors.max_pax }}
                        </div>
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700"
                            >Catatan</label
                        >
                        <textarea
                            v-model="editForm.notes"
                            rows="3"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                        ></textarea>
                        <div
                            v-if="editForm.errors.notes"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ editForm.errors.notes }}
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button
                            type="button"
                            @click="closeEditModal"
                            class="flex-1 rounded-lg border-2 border-gray-300 px-6 py-3 font-semibold text-gray-700 transition hover:bg-gray-50"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="editForm.processing"
                            class="flex-1 rounded-lg bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-3 font-semibold text-white transition hover:shadow-lg disabled:opacity-50"
                        >
                            {{
                                editForm.processing ? 'Menyimpan...' : 'Simpan'
                            }}
                        </button>
                    </div>
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
                    class="mb-4 rounded-lg border border-blue-200 bg-blue-50 p-4"
                >
                    <p class="mb-2 text-sm text-blue-800">Format CSV:</p>
                    <code class="text-xs text-blue-900"
                        >Nama,Telepon,Kategori,Max Pax,Catatan</code
                    >
                    <p class="mt-2 text-xs text-blue-700">
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
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
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
                            class="flex-1 rounded-lg bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-3 font-semibold text-white transition hover:shadow-lg disabled:opacity-50"
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
