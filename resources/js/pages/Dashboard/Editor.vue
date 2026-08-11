<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';

defineOptions({
    layout: undefined,
});

interface GalleryPhoto {
    url: string;
    caption?: string;
}

interface InvitationContent {
    bride_name: string | null;
    bride_nickname: string | null;
    bride_father: string | null;
    bride_mother: string | null;
    bride_photo_url: string | null;
    groom_name: string | null;
    groom_nickname: string | null;
    groom_father: string | null;
    groom_mother: string | null;
    groom_photo_url: string | null;
    cover_name_display: string | null;
    couple_photo_url: string | null;
    akad_datetime: string | null;
    akad_venue: string | null;
    akad_maps_url: string | null;
    reception_datetime: string | null;
    reception_venue: string | null;
    reception_maps_url: string | null;
    show_reception: boolean;
    love_story: string | null;
    special_message: string | null;
    cover_photo_url: string | null;
    video_url: string | null;
    music_url: string | null;
    gallery_photos: GalleryPhoto[] | null;
    bank_name: string | null;
    account_number: string | null;
    account_name: string | null;
    qris_image_url: string | null;
    gopay_number: string | null;
    ovo_number: string | null;
    dana_number: string | null;
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
    content: InvitationContent | null;
}>();

const form = useForm({
    bride_name: props.content?.bride_name || '',
    bride_nickname: props.content?.bride_nickname || '',
    bride_father: props.content?.bride_father || '',
    bride_mother: props.content?.bride_mother || '',
    bride_photo_url: props.content?.bride_photo_url || '',
    groom_name: props.content?.groom_name || '',
    groom_nickname: props.content?.groom_nickname || '',
    groom_father: props.content?.groom_father || '',
    groom_mother: props.content?.groom_mother || '',
    groom_photo_url: props.content?.groom_photo_url || '',
    cover_name_display: props.content?.cover_name_display || 'full',
    couple_photo_url: props.content?.couple_photo_url || '',
    akad_datetime: props.content?.akad_datetime || '',
    akad_venue: props.content?.akad_venue || '',
    akad_maps_url: props.content?.akad_maps_url || '',
    reception_datetime: props.content?.reception_datetime || '',
    reception_venue: props.content?.reception_venue || '',
    reception_maps_url: props.content?.reception_maps_url || '',
    show_reception: props.content?.show_reception ?? true,
    love_story: props.content?.love_story || '',
    special_message: props.content?.special_message || '',
    cover_photo_url: props.content?.cover_photo_url || '',
    video_url: props.content?.video_url || '',
    background_url: props.content?.background_url || '',
    music_url: props.content?.music_url || '',
    music_title: props.content?.music_title || '',
    gallery_photos: props.content?.gallery_photos || [],
    bank_name: props.content?.bank_name || '',
    account_number: props.content?.account_number || '',
    account_name: props.content?.account_name || '',
    qris_image_url: props.content?.qris_image_url || '',
    gopay_number: props.content?.gopay_number || '',
    ovo_number: props.content?.ovo_number || '',
    dana_number: props.content?.dana_number || '',
    gift_address: props.content?.gift_address || '',
});

const uploadingCover = ref(false);
const uploadingBackground = ref(false);
const uploadingMusic = ref(false);
const uploadingQris = ref(false);
const uploadingBride = ref(false);
const uploadingGroom = ref(false);
const uploadingCouple = ref(false);
const uploadingGallery = ref(false);

// Get CSRF token from cookie (works without meta tag)
const getCsrfToken = (): string => {
    return decodeURIComponent(
        document.cookie
            .split('; ')
            .find((row) => row.startsWith('XSRF-TOKEN='))
            ?.split('=')[1] ?? '',
    );
};

const uploadFile = async (
    url: string,
    file: File,
): Promise<{ success: boolean; url?: string; message?: string }> => {
    const formData = new FormData();
    formData.append('file', file);

    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'X-XSRF-TOKEN': getCsrfToken(),
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: formData,
    });

    return response.json();
};

const uploadCover = async (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];

    if (!file) {
        return;
    }

    uploadingCover.value = true;

    try {
        const data = await uploadFile('/media/upload/cover', file);

        if (data.success && data.url) {
            form.cover_photo_url = data.url;
        } else {
            alert(data.message || 'Gagal upload foto cover');
        }
    } catch (error) {
        console.error('Upload error:', error);
        alert('Terjadi kesalahan saat upload foto cover');
    } finally {
        uploadingCover.value = false;
    }
};

const uploadBackground = async (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];

    if (!file) {
        return;
    }

    uploadingBackground.value = true;

    try {
        const data = await uploadFile('/media/upload/background', file);

        if (data.success && data.url) {
            form.background_url = data.url;
        } else {
            alert(data.message || 'Gagal upload background');
        }
    } catch (error) {
        console.error('Upload error:', error);
        alert('Terjadi kesalahan saat upload background');
    } finally {
        uploadingBackground.value = false;
    }
};

const uploadMusic = async (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];

    if (!file) {
        return;
    }

    uploadingMusic.value = true;

    try {
        const data = await uploadFile('/media/upload/music', file);

        if (data.success && data.url) {
            form.music_url = data.url;
        } else {
            alert(data.message || 'Gagal upload musik');
        }
    } catch (error) {
        console.error('Upload error:', error);
        alert('Terjadi kesalahan saat upload musik');
    } finally {
        uploadingMusic.value = false;
    }
};

const uploadQris = async (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];

    if (!file) {
        return;
    }

    uploadingQris.value = true;

    try {
        const data = await uploadFile('/media/upload/qris', file);

        if (data.success && data.url) {
            form.qris_image_url = data.url;
        } else {
            alert(data.message || 'Gagal upload QRIS');
        }
    } catch (error) {
        console.error('Upload error:', error);
        alert('Terjadi kesalahan saat upload QRIS');
    } finally {
        uploadingQris.value = false;
    }
};

const uploadBridePhoto = async (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];

    if (!file) {
        return;
    }

    uploadingBride.value = true;

    try {
        const data = await uploadFile('/media/upload/bride', file);

        if (data.success && data.url) {
            form.bride_photo_url = data.url;
        } else {
            alert(data.message || 'Gagal upload foto mempelai wanita');
        }
    } catch (error) {
        console.error('Upload error:', error);
        alert('Terjadi kesalahan saat upload foto mempelai wanita');
    } finally {
        uploadingBride.value = false;
    }
};

const uploadGroomPhoto = async (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];

    if (!file) {
        return;
    }

    uploadingGroom.value = true;

    try {
        const data = await uploadFile('/media/upload/groom', file);

        if (data.success && data.url) {
            form.groom_photo_url = data.url;
        } else {
            alert(data.message || 'Gagal upload foto mempelai pria');
        }
    } catch (error) {
        console.error('Upload error:', error);
        alert('Terjadi kesalahan saat upload foto mempelai pria');
    } finally {
        uploadingGroom.value = false;
    }
};

const uploadCouplePhoto = async (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];

    if (!file) {
        return;
    }

    uploadingCouple.value = true;

    try {
        const data = await uploadFile('/media/upload/couple', file);

        if (data.success && data.url) {
            form.couple_photo_url = data.url;
        } else {
            alert(data.message || 'Gagal upload foto pasangan');
        }
    } catch (error) {
        console.error('Upload error:', error);
        alert('Terjadi kesalahan saat upload foto pasangan');
    } finally {
        uploadingCouple.value = false;
    }
};

const uploadGalleryPhoto = async (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];

    if (!file) {
        return;
    }

    uploadingGallery.value = true;

    try {
        const data = await uploadFile('/media/upload/gallery', file);

        if (data.success && data.url) {
            if (!Array.isArray(form.gallery_photos)) {
                form.gallery_photos = [];
            }

            form.gallery_photos.push({ url: data.url, caption: '' });
        } else {
            alert(data.message || 'Gagal upload foto galeri');
        }
    } catch (error) {
        console.error('Upload error:', error);
        alert('Terjadi kesalahan saat upload foto galeri');
    } finally {
        uploadingGallery.value = false;
    }
};

const removeGalleryPhoto = (index: number) => {
    if (Array.isArray(form.gallery_photos)) {
        form.gallery_photos.splice(index, 1);
    }
};

const submit = () => {
    console.log('Submitting form with data:', form.data());
    console.log('Form errors before submit:', form.errors);

    form.post('/dashboard/editor', {
        preserveScroll: true,
        onBefore: () => {
            console.log('Form submission started');
        },
        onError: (errors) => {
            console.error('Validation errors:', errors);

            // Scroll to top to show error summary
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        onSuccess: (page) => {
            console.log('Form submitted successfully', page);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        onFinish: () => {
            console.log('Form submission finished');
        },
    });
};
</script>

<template>
    <DashboardLayout>
        <Head title="Edit Konten Undangan" />

        <div class="min-h-screen bg-gray-50">
            <!-- Main Content -->
            <div class="container mx-auto px-4 py-8">
                <!-- Header -->
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <Link
                            href="/dashboard"
                            class="mb-2 inline-flex items-center text-sm text-gray-600 hover:text-green-600"
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
                            Edit Konten Undangan
                        </h1>
                        <p class="mt-1 text-gray-600">
                            Template: {{ invitation.template.name }}
                        </p>
                    </div>
                </div>

                <!-- Flash Messages -->
                <div
                    v-if="$page.props.flash?.success"
                    class="mb-6 flex items-center rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800"
                >
                    <svg
                        class="mr-2 h-5 w-5"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    {{ $page.props.flash.success }}
                </div>

                <div
                    v-if="$page.props.flash?.error"
                    class="mb-6 flex items-center rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800"
                >
                    <svg
                        class="mr-2 h-5 w-5"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    {{ $page.props.flash.error }}
                </div>

                <!-- Validation Errors Summary -->
                <div
                    v-if="Object.keys(form.errors).length > 0"
                    class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4"
                >
                    <div class="flex items-start">
                        <svg
                            class="mt-0.5 mr-2 h-5 w-5 text-red-600"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <div class="flex-1">
                            <h3 class="mb-2 text-sm font-semibold text-red-800">
                                Terdapat kesalahan pada form:
                            </h3>
                            <ul
                                class="list-inside list-disc space-y-1 text-sm text-red-700"
                            >
                                <li
                                    v-for="(error, field) in form.errors"
                                    :key="field"
                                >
                                    {{ error }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="max-w-4xl">
                    <!-- Mempelai Wanita -->
                    <div class="mb-6 rounded-xl bg-white p-6 shadow-md">
                        <h2 class="mb-4 text-xl font-bold text-gray-900">
                            Mempelai Wanita
                        </h2>

                        <div class="space-y-4">
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                    >Nama Lengkap *</label
                                >
                                <input
                                    v-model="form.bride_name"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                    placeholder="Contoh: Siti Nurhaliza"
                                    required
                                />
                                <p
                                    v-if="form.errors.bride_name"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.bride_name }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                    >Nama Panggilan</label
                                >
                                <input
                                    v-model="form.bride_nickname"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                    placeholder="Contoh: Yeli"
                                />
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label
                                        class="mb-2 block text-sm font-medium text-gray-700"
                                        >Nama Ayah</label
                                    >
                                    <input
                                        v-model="form.bride_father"
                                        type="text"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                        placeholder="Contoh: Bapak Ahmad"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-medium text-gray-700"
                                        >Nama Ibu</label
                                    >
                                    <input
                                        v-model="form.bride_mother"
                                        type="text"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                        placeholder="Contoh: Ibu Siti"
                                    />
                                </div>
                            </div>

                            <!-- Bride Photo -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                    >Foto Mempelai Wanita</label
                                >
                                <div class="flex items-start space-x-4">
                                    <div
                                        v-if="form.bride_photo_url"
                                        class="relative h-40 w-32 overflow-hidden rounded-lg border-2 border-gray-200"
                                    >
                                        <img
                                            :src="form.bride_photo_url"
                                            alt="Bride"
                                            class="h-full w-full object-cover"
                                        />
                                        <button
                                            type="button"
                                            @click="form.bride_photo_url = ''"
                                            class="absolute top-2 right-2 rounded-full bg-red-500 p-1 text-white hover:bg-red-600"
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
                                                    d="M6 18L18 6M6 6l12 12"
                                                />
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="flex-1">
                                        <input
                                            type="file"
                                            @change="uploadBridePhoto"
                                            accept="image/*"
                                            class="hidden"
                                            id="bride-upload"
                                            :disabled="uploadingBride"
                                        />
                                        <label
                                            for="bride-upload"
                                            class="inline-flex cursor-pointer items-center rounded-lg border border-gray-300 px-4 py-2 transition hover:bg-gray-50"
                                            :class="{
                                                'cursor-not-allowed opacity-50':
                                                    uploadingBride,
                                            }"
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
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                />
                                            </svg>
                                            {{
                                                uploadingBride
                                                    ? 'Uploading...'
                                                    : 'Pilih Foto'
                                            }}
                                        </label>
                                        <p class="mt-2 text-xs text-gray-500">
                                            Format: JPG, PNG, WebP. Maksimal
                                            5MB. Rasio potret (3:4)
                                            direkomendasikan
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mempelai Pria -->
                    <div class="mb-6 rounded-xl bg-white p-6 shadow-md">
                        <h2 class="mb-4 text-xl font-bold text-gray-900">
                            Mempelai Pria
                        </h2>

                        <div class="space-y-4">
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                    >Nama Lengkap *</label
                                >
                                <input
                                    v-model="form.groom_name"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                    placeholder="Contoh: Muhammad Rizki"
                                    required
                                />
                                <p
                                    v-if="form.errors.groom_name"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.groom_name }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                    >Nama Panggilan</label
                                >
                                <input
                                    v-model="form.groom_nickname"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                    placeholder="Contoh: Redho"
                                />
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label
                                        class="mb-2 block text-sm font-medium text-gray-700"
                                        >Nama Ayah</label
                                    >
                                    <input
                                        v-model="form.groom_father"
                                        type="text"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                        placeholder="Contoh: Bapak Budi"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-medium text-gray-700"
                                        >Nama Ibu</label
                                    >
                                    <input
                                        v-model="form.groom_mother"
                                        type="text"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                        placeholder="Contoh: Ibu Ani"
                                    />
                                </div>
                            </div>

                            <!-- Groom Photo -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                    >Foto Mempelai Pria</label
                                >
                                <div class="flex items-start space-x-4">
                                    <div
                                        v-if="form.groom_photo_url"
                                        class="relative h-40 w-32 overflow-hidden rounded-lg border-2 border-gray-200"
                                    >
                                        <img
                                            :src="form.groom_photo_url"
                                            alt="Groom"
                                            class="h-full w-full object-cover"
                                        />
                                        <button
                                            type="button"
                                            @click="form.groom_photo_url = ''"
                                            class="absolute top-2 right-2 rounded-full bg-red-500 p-1 text-white hover:bg-red-600"
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
                                                    d="M6 18L18 6M6 6l12 12"
                                                />
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="flex-1">
                                        <input
                                            type="file"
                                            @change="uploadGroomPhoto"
                                            accept="image/*"
                                            class="hidden"
                                            id="groom-upload"
                                            :disabled="uploadingGroom"
                                        />
                                        <label
                                            for="groom-upload"
                                            class="inline-flex cursor-pointer items-center rounded-lg border border-gray-300 px-4 py-2 transition hover:bg-gray-50"
                                            :class="{
                                                'cursor-not-allowed opacity-50':
                                                    uploadingGroom,
                                            }"
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
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                />
                                            </svg>
                                            {{
                                                uploadingGroom
                                                    ? 'Uploading...'
                                                    : 'Pilih Foto'
                                            }}
                                        </label>
                                        <p class="mt-2 text-xs text-gray-500">
                                            Format: JPG, PNG, WebP. Maksimal
                                            5MB. Rasio potret (3:4)
                                            direkomendasikan
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Couple Photo -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                    >Foto Pasangan</label
                                >
                                <div class="flex items-start space-x-4">
                                    <div
                                        v-if="form.couple_photo_url"
                                        class="relative h-40 w-32 overflow-hidden rounded-lg border-2 border-gray-200"
                                    >
                                        <img
                                            :src="form.couple_photo_url"
                                            alt="Couple"
                                            class="h-full w-full object-cover"
                                        />
                                        <button
                                            type="button"
                                            @click="form.couple_photo_url = ''"
                                            class="absolute top-2 right-2 rounded-full bg-red-500 p-1 text-white hover:bg-red-600"
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
                                                    d="M6 18L18 6M6 6l12 12"
                                                />
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="flex-1">
                                        <input
                                            type="file"
                                            @change="uploadCouplePhoto"
                                            accept="image/*"
                                            class="hidden"
                                            id="couple-upload"
                                            :disabled="uploadingCouple"
                                        />
                                        <label
                                            for="couple-upload"
                                            class="inline-flex cursor-pointer items-center rounded-lg border border-gray-300 px-4 py-2 transition hover:bg-gray-50"
                                            :class="{
                                                'cursor-not-allowed opacity-50':
                                                    uploadingCouple,
                                            }"
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
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                />
                                            </svg>
                                            {{
                                                uploadingCouple
                                                    ? 'Uploading...'
                                                    : 'Pilih Foto'
                                            }}
                                        </label>
                                        <p class="mt-2 text-xs text-gray-500">
                                            Format: JPG, PNG, WebP. Maksimal
                                            5MB. Foto ini tampil sebagai
                                            transisi di section Foto Pasangan
                                            dan di bagian Closing undangan
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Akad Nikah -->
                    <div class="mb-6 rounded-xl bg-white p-6 shadow-md">
                        <h2 class="mb-4 text-xl font-bold text-gray-900">
                            Akad Nikah
                        </h2>

                        <div class="space-y-4">
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                    >Tanggal & Waktu *</label
                                >
                                <input
                                    v-model="form.akad_datetime"
                                    type="datetime-local"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                    required
                                />
                                <p
                                    v-if="form.errors.akad_datetime"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.akad_datetime }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                    >Nama Tempat *</label
                                >
                                <input
                                    v-model="form.akad_venue"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                    placeholder="Contoh: Masjid Al-Ikhlas"
                                    required
                                />
                                <p
                                    v-if="form.errors.akad_venue"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.akad_venue }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                    >Link Google Maps</label
                                >
                                <input
                                    v-model="form.akad_maps_url"
                                    type="url"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                    placeholder="https://maps.google.com/..."
                                />
                                <p
                                    v-if="form.errors.akad_maps_url"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.akad_maps_url }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Resepsi -->
                    <div class="mb-6 rounded-xl bg-white p-6 shadow-md">
                        <div
                            class="mb-4 flex items-center justify-between gap-4"
                        >
                            <h2 class="text-xl font-bold text-gray-900">
                                Resepsi (Opsional)
                            </h2>
                            <label
                                class="flex cursor-pointer items-center gap-2"
                            >
                                <input
                                    v-model="form.show_reception"
                                    type="checkbox"
                                    class="h-5 w-5 rounded border-gray-300 text-green-600 focus:ring-green-500"
                                />
                                <span class="text-sm font-medium text-gray-700"
                                    >Tampilkan Resepsi di undangan</span
                                >
                            </label>
                        </div>

                        <p
                            v-if="!form.show_reception"
                            class="mb-4 rounded-lg bg-gray-100 p-3 text-sm text-gray-600"
                        >
                            Resepsi disembunyikan. Undangan hanya menampilkan
                            akad nikah.
                        </p>

                        <div v-if="form.show_reception" class="space-y-4">
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                    >Tanggal & Waktu</label
                                >
                                <input
                                    v-model="form.reception_datetime"
                                    type="datetime-local"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                />
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                    >Nama Tempat</label
                                >
                                <input
                                    v-model="form.reception_venue"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                    placeholder="Contoh: Gedung Serbaguna"
                                />
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                    >Link Google Maps</label
                                >
                                <input
                                    v-model="form.reception_maps_url"
                                    type="url"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                    placeholder="https://maps.google.com/..."
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Media -->
                    <div class="mb-6 rounded-xl bg-white p-6 shadow-md">
                        <h2 class="mb-4 text-xl font-bold text-gray-900">
                            Media
                        </h2>

                        <div class="space-y-6">
                            <!-- Cover Photo -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                    >Foto Cover</label
                                >
                                <div class="flex items-start space-x-4">
                                    <div
                                        v-if="form.cover_photo_url"
                                        class="relative h-32 w-48 overflow-hidden rounded-lg border-2 border-gray-200"
                                    >
                                        <img
                                            :src="form.cover_photo_url"
                                            alt="Cover"
                                            class="h-full w-full object-cover"
                                        />
                                        <button
                                            type="button"
                                            @click="form.cover_photo_url = ''"
                                            class="absolute top-2 right-2 rounded-full bg-red-500 p-1 text-white hover:bg-red-600"
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
                                                    d="M6 18L18 6M6 6l12 12"
                                                />
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="flex-1">
                                        <input
                                            type="file"
                                            @change="uploadCover"
                                            accept="image/*"
                                            class="hidden"
                                            id="cover-upload"
                                            :disabled="uploadingCover"
                                        />
                                        <label
                                            for="cover-upload"
                                            class="inline-flex cursor-pointer items-center rounded-lg border border-gray-300 px-4 py-2 transition hover:bg-gray-50"
                                            :class="{
                                                'cursor-not-allowed opacity-50':
                                                    uploadingCover,
                                            }"
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
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                />
                                            </svg>
                                            {{
                                                uploadingCover
                                                    ? 'Uploading...'
                                                    : 'Pilih Foto'
                                            }}
                                        </label>
                                        <p class="mt-2 text-xs text-gray-500">
                                            Format: JPG, PNG, WebP. Maksimal 5MB
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Page Background -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                    >Background Halaman</label
                                >
                                <div class="flex items-start space-x-4">
                                    <div
                                        v-if="form.background_url"
                                        class="relative h-32 w-48 overflow-hidden rounded-lg border-2 border-gray-200"
                                    >
                                        <img
                                            :src="form.background_url"
                                            alt="Background"
                                            class="h-full w-full object-cover"
                                        />
                                        <button
                                            type="button"
                                            @click="form.background_url = ''"
                                            class="absolute top-2 right-2 rounded-full bg-red-500 p-1 text-white hover:bg-red-600"
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
                                                    d="M6 18L18 6M6 6l12 12"
                                                />
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="flex-1">
                                        <input
                                            type="file"
                                            @change="uploadBackground"
                                            accept="image/*"
                                            class="hidden"
                                            id="background-upload"
                                            :disabled="uploadingBackground"
                                        />
                                        <label
                                            for="background-upload"
                                            class="inline-flex cursor-pointer items-center rounded-lg border border-gray-300 px-4 py-2 transition hover:bg-gray-50"
                                            :class="{
                                                'cursor-not-allowed opacity-50':
                                                    uploadingBackground,
                                            }"
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
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                />
                                            </svg>
                                            {{
                                                uploadingBackground
                                                    ? 'Uploading...'
                                                    : 'Pilih Gambar'
                                            }}
                                        </label>
                                        <p class="mt-2 text-xs text-gray-500">
                                            Gambar latar halaman undangan
                                            (terlihat di area sekitar undangan
                                            dan layar pembuka). Format: JPG,
                                            PNG, WebP. Maksimal 5MB
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Video Prewedding -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                    >Video Prewedding (YouTube)</label
                                >
                                <input
                                    type="url"
                                    v-model="form.video_url"
                                    placeholder="https://www.youtube.com/watch?v=..."
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                />
                                <p class="mt-2 text-xs text-gray-500">
                                    Tempel link video YouTube (mis. video
                                    prewedding). Video akan diputar otomatis
                                    sebagai background di halaman pembuka
                                    (hero). Kosongkan jika hanya ingin memakai
                                    Foto Cover.
                                </p>
                            </div>

                            <!-- Cover Name Display -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                    >Tampilan Nama di Cover</label
                                >
                                <select
                                    v-model="form.cover_name_display"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                >
                                    <option value="full">
                                        Nama Lengkap (Contoh: Siti Nurhaliza
                                        &amp; Raffi Ahmad)
                                    </option>
                                    <option value="nickname">
                                        Nama Panggilan (Contoh: Yeli &amp;
                                        Redho)
                                    </option>
                                    <option value="initials">
                                        Inisial (Contoh: S &amp; R)
                                    </option>
                                </select>
                                <p class="mt-2 text-xs text-gray-500">
                                    Nama yang tampil di halaman pembuka
                                    undangan.
                                </p>
                            </div>

                            <!-- Music -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                    >Musik Latar</label
                                >
                                <div class="space-y-3">
                                    <div
                                        v-if="form.music_url"
                                        class="flex items-center space-x-3 rounded-lg bg-gray-50 p-3"
                                    >
                                        <svg
                                            class="h-6 w-6 text-green-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"
                                            />
                                        </svg>
                                        <div class="flex-1">
                                            <p
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                Musik telah diupload
                                            </p>
                                            <audio controls class="mt-2 w-full">
                                                <source
                                                    :src="form.music_url"
                                                    type="audio/mpeg"
                                                />
                                            </audio>
                                        </div>
                                        <button
                                            type="button"
                                            @click="form.music_url = ''"
                                            class="text-red-600 hover:text-red-700"
                                        >
                                            <svg
                                                class="h-5 w-5"
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
                                        </button>
                                    </div>

                                    <div>
                                        <input
                                            type="file"
                                            @change="uploadMusic"
                                            accept="audio/*"
                                            class="hidden"
                                            id="music-upload"
                                            :disabled="uploadingMusic"
                                        />
                                        <label
                                            for="music-upload"
                                            class="inline-flex cursor-pointer items-center rounded-lg border border-gray-300 px-4 py-2 transition hover:bg-gray-50"
                                            :class="{
                                                'cursor-not-allowed opacity-50':
                                                    uploadingMusic,
                                            }"
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
                                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                                                />
                                            </svg>
                                            {{
                                                uploadingMusic
                                                    ? 'Uploading...'
                                                    : 'Upload Musik'
                                            }}
                                        </label>
                                        <p class="mt-2 text-xs text-gray-500">
                                            Format: MP3, WAV. Maksimal 10MB
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Music Title -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                    >Judul Musik</label
                                >
                                <input
                                    v-model="form.music_title"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                    placeholder="Contoh: Sepanjang Hidup - Maher Zain"
                                />
                                <p
                                    v-if="form.errors.music_title"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.music_title }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    Nama lagu yang tampil di kontrol musik
                                    undangan
                                </p>
                            </div>

                            <!-- Gallery Photos -->
                            <div class="border-t pt-6">
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                    >Galeri Foto</label
                                >

                                <!-- Gallery Grid -->
                                <div
                                    v-if="
                                        Array.isArray(form.gallery_photos) &&
                                        form.gallery_photos.length > 0
                                    "
                                    class="mb-4 grid grid-cols-2 gap-4 md:grid-cols-4"
                                >
                                    <div
                                        v-for="(
                                            photo, index
                                        ) in form.gallery_photos"
                                        :key="index"
                                        class="group relative"
                                    >
                                        <div
                                            class="aspect-square overflow-hidden rounded-lg border-2 border-gray-200"
                                        >
                                            <img
                                                :src="photo.url"
                                                :alt="
                                                    photo.caption ||
                                                    `Gallery ${index + 1}`
                                                "
                                                class="h-full w-full object-cover"
                                            />
                                        </div>
                                        <button
                                            type="button"
                                            @click="removeGalleryPhoto(index)"
                                            class="absolute top-2 right-2 rounded-full bg-red-500 p-1.5 text-white opacity-0 transition-opacity group-hover:opacity-100 hover:bg-red-600"
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
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                />
                                            </svg>
                                        </button>
                                        <!-- Caption Input -->
                                        <input
                                            v-model="photo.caption"
                                            type="text"
                                            class="mt-2 w-full rounded border border-gray-300 px-2 py-1 text-xs focus:border-transparent focus:ring-1 focus:ring-green-500"
                                            placeholder="Caption (opsional)"
                                        />
                                    </div>
                                </div>

                                <!-- Upload Button -->
                                <div>
                                    <input
                                        type="file"
                                        @change="uploadGalleryPhoto"
                                        accept="image/*"
                                        class="hidden"
                                        id="gallery-upload"
                                        :disabled="uploadingGallery"
                                    />
                                    <label
                                        for="gallery-upload"
                                        class="inline-flex cursor-pointer items-center rounded-lg border border-gray-300 px-4 py-2 transition hover:bg-gray-50"
                                        :class="{
                                            'cursor-not-allowed opacity-50':
                                                uploadingGallery,
                                        }"
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
                                                d="M12 4v16m8-8H4"
                                            />
                                        </svg>
                                        {{
                                            uploadingGallery
                                                ? 'Uploading...'
                                                : 'Tambah Foto Galeri'
                                        }}
                                    </label>
                                    <p class="mt-2 text-xs text-gray-500">
                                        Format: JPG, PNG, WebP. Maksimal 5MB per
                                        foto. Rasio persegi (1:1)
                                        direkomendasikan
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cerita & Pesan -->
                    <div class="mb-6 rounded-xl bg-white p-6 shadow-md">
                        <h2 class="mb-4 text-xl font-bold text-gray-900">
                            Cerita & Pesan
                        </h2>

                        <div class="space-y-4">
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                    >Cerita Cinta</label
                                >
                                <textarea
                                    v-model="form.love_story"
                                    rows="5"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                    placeholder="Ceritakan kisah cinta Anda..."
                                ></textarea>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                    >Pesan Khusus</label
                                >
                                <textarea
                                    v-model="form.special_message"
                                    rows="3"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                    placeholder="Pesan untuk tamu undangan..."
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Amplop Digital -->
                    <div class="mb-6 rounded-xl bg-white p-6 shadow-md">
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-bold text-gray-900">
                                    Amplop Digital
                                </h2>
                                <p class="mt-1 text-sm text-gray-600">
                                    Terima hadiah dari tamu secara digital
                                </p>
                            </div>
                            <div
                                class="rounded-lg bg-gradient-to-r from-green-100 to-emerald-100 px-4 py-2"
                            >
                                <span
                                    class="text-sm font-semibold text-green-700"
                                    >Opsional</span
                                >
                            </div>
                        </div>

                        <div class="space-y-6">
                            <!-- Transfer Bank -->
                            <div class="border-t pt-6">
                                <h3
                                    class="mb-4 flex items-center text-lg font-semibold text-gray-900"
                                >
                                    <svg
                                        class="mr-2 h-5 w-5 text-green-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                                        />
                                    </svg>
                                    Transfer Bank
                                </h3>

                                <div class="grid gap-4 md:grid-cols-3">
                                    <div>
                                        <label
                                            class="mb-2 block text-sm font-medium text-gray-700"
                                            >Nama Bank</label
                                        >
                                        <input
                                            v-model="form.bank_name"
                                            type="text"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                            placeholder="Contoh: BCA"
                                        />
                                    </div>

                                    <div>
                                        <label
                                            class="mb-2 block text-sm font-medium text-gray-700"
                                            >Nomor Rekening</label
                                        >
                                        <input
                                            v-model="form.account_number"
                                            type="text"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                            placeholder="1234567890"
                                        />
                                    </div>

                                    <div>
                                        <label
                                            class="mb-2 block text-sm font-medium text-gray-700"
                                            >Nama Pemilik Rekening</label
                                        >
                                        <input
                                            v-model="form.account_name"
                                            type="text"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                            placeholder="Nama sesuai rekening"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- QRIS -->
                            <div class="border-t pt-6">
                                <h3
                                    class="mb-4 flex items-center text-lg font-semibold text-gray-900"
                                >
                                    <svg
                                        class="mr-2 h-5 w-5 text-green-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"
                                        />
                                    </svg>
                                    QRIS
                                </h3>

                                <div class="flex items-start space-x-4">
                                    <div
                                        v-if="form.qris_image_url"
                                        class="relative h-48 w-48 overflow-hidden rounded-lg border-2 border-gray-200"
                                    >
                                        <img
                                            :src="form.qris_image_url"
                                            alt="QRIS"
                                            class="h-full w-full object-cover"
                                        />
                                        <button
                                            type="button"
                                            @click="form.qris_image_url = ''"
                                            class="absolute top-2 right-2 rounded-full bg-red-500 p-1 text-white hover:bg-red-600"
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
                                                    d="M6 18L18 6M6 6l12 12"
                                                />
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="flex-1">
                                        <input
                                            type="file"
                                            @change="uploadQris"
                                            accept="image/*"
                                            class="hidden"
                                            id="qris-upload"
                                            :disabled="uploadingQris"
                                        />
                                        <label
                                            for="qris-upload"
                                            class="inline-flex cursor-pointer items-center rounded-lg border border-gray-300 px-4 py-2 transition hover:bg-gray-50"
                                            :class="{
                                                'cursor-not-allowed opacity-50':
                                                    uploadingQris,
                                            }"
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
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                />
                                            </svg>
                                            {{
                                                uploadingQris
                                                    ? 'Uploading...'
                                                    : 'Upload QRIS'
                                            }}
                                        </label>
                                        <p class="mt-2 text-xs text-gray-500">
                                            Upload gambar QR Code untuk
                                            pembayaran QRIS
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- E-Wallet -->
                            <div class="border-t pt-6">
                                <h3
                                    class="mb-4 flex items-center text-lg font-semibold text-gray-900"
                                >
                                    <svg
                                        class="mr-2 h-5 w-5 text-green-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"
                                        />
                                    </svg>
                                    E-Wallet
                                </h3>

                                <div class="grid gap-4 md:grid-cols-3">
                                    <div>
                                        <label
                                            class="mb-2 block flex items-center text-sm font-medium text-gray-700"
                                        >
                                            <span
                                                class="mr-2 rounded bg-blue-500 px-2 py-0.5 text-xs font-bold text-white"
                                                >GoPay</span
                                            >
                                            Nomor HP
                                        </label>
                                        <input
                                            v-model="form.gopay_number"
                                            type="text"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                            placeholder="08123456789"
                                        />
                                    </div>

                                    <div>
                                        <label
                                            class="mb-2 block flex items-center text-sm font-medium text-gray-700"
                                        >
                                            <span
                                                class="mr-2 rounded bg-green-600 px-2 py-0.5 text-xs font-bold text-white"
                                                >OVO</span
                                            >
                                            Nomor HP
                                        </label>
                                        <input
                                            v-model="form.ovo_number"
                                            type="text"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                            placeholder="08123456789"
                                        />
                                    </div>

                                    <div>
                                        <label
                                            class="mb-2 block flex items-center text-sm font-medium text-gray-700"
                                        >
                                            <span
                                                class="mr-2 rounded bg-blue-400 px-2 py-0.5 text-xs font-bold text-white"
                                                >DANA</span
                                            >
                                            Nomor HP
                                        </label>
                                        <input
                                            v-model="form.dana_number"
                                            type="text"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                            placeholder="08123456789"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="border-t pt-4">
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                    >Alamat Kirim Hadiah</label
                                >
                                <textarea
                                    v-model="form.gift_address"
                                    rows="3"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                    placeholder="Contoh: Jl. Melati No. 12, Kec. Telanaipura, Kota Jambi"
                                ></textarea>
                                <p
                                    v-if="form.errors.gift_address"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.gift_address }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    Alamat pengiriman kado, tampil di section
                                    Amplop Digital / Hadiah undangan
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-lg bg-gradient-to-r from-green-600 to-emerald-600 px-8 py-3 font-semibold text-white transition hover:shadow-lg disabled:opacity-50"
                            >
                                {{
                                    form.processing
                                        ? 'Menyimpan...'
                                        : 'Simpan Perubahan'
                                }}
                            </button>

                            <Link
                                href="/dashboard"
                                class="font-medium text-gray-600 hover:text-gray-800"
                            >
                                Batal
                            </Link>
                        </div>

                        <Link
                            :href="`/dashboard/editor/preview`"
                            target="_blank"
                            class="flex items-center rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white transition hover:bg-blue-700"
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
                            Preview
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>
