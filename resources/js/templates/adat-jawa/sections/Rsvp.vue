<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface InvitationContent {
    bride_name: string;
    groom_name: string;
}

const props = defineProps<{
    content: InvitationContent;
    guestName?: string | null;
    invitationId: number;
    subdomain: string;
}>();

const showSuccess = ref(false);

const form = useForm({
    name: props.guestName || '',
    attendance: 'yes',
    pax_count: 1,
    message: '',
});

const submit = () => {
    form.post(`/i/${props.subdomain}/rsvp`, {
        preserveScroll: true,
        onSuccess: () => {
            showSuccess.value = true;
            form.reset('message');
            setTimeout(() => {
                showSuccess.value = false;
            }, 5000);
        },
    });
};
</script>

<template>
    <section
        id="rsvp"
        class="bg-gradient-to-b from-amber-50 to-amber-100 px-4 py-20"
    >
        <div class="mx-auto max-w-2xl">
            <!-- Header -->
            <div class="mb-12 text-center">
                <h2 class="mb-4 font-serif text-4xl text-amber-900">
                    Konfirmasi Kehadiran
                </h2>
                <div class="mx-auto mb-6 h-1 w-24 bg-amber-600"></div>
                <p class="text-amber-800">
                    Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila
                    Bapak/Ibu/Saudara/i berkenan hadir untuk memberikan doa
                    restu kepada kedua mempelai.
                </p>
            </div>

            <!-- Success Message -->
            <div
                v-if="showSuccess"
                class="mb-6 rounded-lg border border-green-400 bg-green-100 p-4 text-green-700"
            >
                ✓ Terima kasih! Konfirmasi kehadiran Anda telah kami terima.
            </div>

            <!-- RSVP Form -->
            <form
                @submit.prevent="submit"
                class="rounded-lg border-2 border-amber-200 bg-white p-8 shadow-lg"
            >
                <!-- Name -->
                <div class="mb-6">
                    <label class="mb-2 block font-medium text-amber-900">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full rounded-lg border-2 border-amber-200 px-4 py-3 focus:border-amber-500 focus:outline-none"
                        placeholder="Masukkan nama Anda"
                    />
                </div>

                <!-- Attendance -->
                <div class="mb-6">
                    <label class="mb-2 block font-medium text-amber-900">
                        Konfirmasi Kehadiran <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-4">
                        <label
                            class="flex cursor-pointer items-center justify-center rounded-lg border-2 p-4 transition"
                            :class="
                                form.attendance === 'yes'
                                    ? 'border-amber-600 bg-amber-50'
                                    : 'border-amber-200 hover:border-amber-400'
                            "
                        >
                            <input
                                v-model="form.attendance"
                                type="radio"
                                value="yes"
                                class="mr-3"
                            />
                            <span class="font-medium text-amber-900"
                                >Hadir</span
                            >
                        </label>
                        <label
                            class="flex cursor-pointer items-center justify-center rounded-lg border-2 p-4 transition"
                            :class="
                                form.attendance === 'no'
                                    ? 'border-amber-600 bg-amber-50'
                                    : 'border-amber-200 hover:border-amber-400'
                            "
                        >
                            <input
                                v-model="form.attendance"
                                type="radio"
                                value="no"
                                class="mr-3"
                            />
                            <span class="font-medium text-amber-900"
                                >Tidak Hadir</span
                            >
                        </label>
                    </div>
                </div>

                <!-- Pax Count (only if attending) -->
                <div v-if="form.attendance === 'yes'" class="mb-6">
                    <label class="mb-2 block font-medium text-amber-900">
                        Jumlah Tamu <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model.number="form.pax_count"
                        type="number"
                        min="1"
                        max="10"
                        required
                        class="w-full rounded-lg border-2 border-amber-200 px-4 py-3 focus:border-amber-500 focus:outline-none"
                    />
                    <p class="mt-1 text-sm text-amber-600">
                        Termasuk Anda sendiri
                    </p>
                </div>

                <!-- Message -->
                <div class="mb-6">
                    <label class="mb-2 block font-medium text-amber-900">
                        Ucapan & Doa
                    </label>
                    <textarea
                        v-model="form.message"
                        rows="4"
                        class="w-full resize-none rounded-lg border-2 border-amber-200 px-4 py-3 focus:border-amber-500 focus:outline-none"
                        placeholder="Berikan ucapan dan doa untuk kedua mempelai..."
                    ></textarea>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded-lg bg-amber-700 py-4 font-medium text-white transition hover:bg-amber-800 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <span v-if="form.processing">Mengirim...</span>
                    <span v-else>Kirim Konfirmasi</span>
                </button>
            </form>
        </div>
    </section>
</template>
