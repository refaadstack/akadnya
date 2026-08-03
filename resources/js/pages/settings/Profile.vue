<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import { send } from '@/routes/verification';

type Props = {
    mustVerifyEmail: boolean;
    status?: string;
};

defineProps<Props>();

defineOptions({
    layout: undefined,
});

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <DashboardLayout>
        <Head title="Pengaturan Profil" />

        <div class="container mx-auto max-w-3xl px-4 py-8">
            <h1
                class="font-display mb-6 text-2xl font-bold text-[var(--my-neutral)]"
            >
                Pengaturan Profil
            </h1>

            <div class="my-card flex flex-col gap-6 p-6">
                <div>
                    <h2 class="text-lg font-bold text-[var(--my-neutral)]">
                        Informasi Profil
                    </h2>
                    <p class="text-sm text-[var(--my-muted)]">
                        Perbarui nama dan alamat email Anda
                    </p>
                </div>

                <Form
                    v-bind="ProfileController.update.form()"
                    class="space-y-6"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label for="name">Nama</Label>
                        <Input
                            id="name"
                            class="mt-1 block w-full"
                            name="name"
                            :default-value="user.name"
                            required
                            autocomplete="name"
                            placeholder="Nama lengkap"
                        />
                        <InputError class="mt-2" :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Alamat email</Label>
                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            name="email"
                            :default-value="user.email"
                            required
                            autocomplete="username"
                            placeholder="Alamat email"
                        />
                        <InputError class="mt-2" :message="errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="text-sm text-[var(--my-muted)]">
                            Alamat email Anda belum diverifikasi.
                            <Link
                                :href="send()"
                                as="button"
                                class="font-semibold text-[var(--my-primary)] underline underline-offset-4 transition hover:text-[var(--my-primary-strong)]"
                            >
                                Klik di sini untuk mengirim ulang email
                                verifikasi.
                            </Link>
                        </p>

                        <div
                            v-if="status === 'verification-link-sent'"
                            class="mt-2 text-sm font-medium text-green-600"
                        >
                            Tautan verifikasi baru telah dikirim ke alamat email
                            Anda.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            :disabled="processing"
                            data-test="update-profile-button"
                        >
                            Simpan
                        </Button>
                    </div>
                </Form>
            </div>

            <div class="mt-6">
                <DeleteUser />
            </div>
        </div>
    </DashboardLayout>
</template>
