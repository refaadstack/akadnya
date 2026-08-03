<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ShieldCheck } from 'lucide-vue-next';
import { onUnmounted, ref } from 'vue';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TwoFactorRecoveryCodes from '@/components/TwoFactorRecoveryCodes.vue';
import TwoFactorSetupModal from '@/components/TwoFactorSetupModal.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import { disable, enable } from '@/routes/two-factor';

type Props = {
    canManageTwoFactor?: boolean;
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
};

withDefaults(defineProps<Props>(), {
    canManageTwoFactor: false,
    requiresConfirmation: false,
    twoFactorEnabled: false,
});

defineOptions({
    layout: undefined,
});

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref<boolean>(false);

onUnmounted(() => clearTwoFactorAuthData());
</script>

<template>
    <DashboardLayout>
        <Head title="Keamanan" />

        <div class="container mx-auto max-w-3xl px-4 py-8">
            <h1
                class="font-display mb-6 text-2xl font-bold text-[var(--my-neutral)]"
            >
                Keamanan
            </h1>

            <div class="my-card flex flex-col gap-6 p-6">
                <div>
                    <h2 class="text-lg font-bold text-[var(--my-neutral)]">
                        Perbarui Kata Sandi
                    </h2>
                    <p class="text-sm text-[var(--my-muted)]">
                        Pastikan akun Anda menggunakan kata sandi yang panjang
                        dan acak agar tetap aman
                    </p>
                </div>

                <Form
                    v-bind="SecurityController.update.form()"
                    :options="{
                        preserveScroll: true,
                    }"
                    reset-on-success
                    :reset-on-error="[
                        'password',
                        'password_confirmation',
                        'current_password',
                    ]"
                    class="space-y-6"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label for="current_password"
                            >Kata sandi saat ini</Label
                        >
                        <PasswordInput
                            id="current_password"
                            name="current_password"
                            class="mt-1 block w-full"
                            autocomplete="current-password"
                            placeholder="Kata sandi saat ini"
                        />
                        <InputError :message="errors.current_password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">Kata sandi baru</Label>
                        <PasswordInput
                            id="password"
                            name="password"
                            class="mt-1 block w-full"
                            autocomplete="new-password"
                            placeholder="Kata sandi baru"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation"
                            >Konfirmasi kata sandi baru</Label
                        >
                        <PasswordInput
                            id="password_confirmation"
                            name="password_confirmation"
                            class="mt-1 block w-full"
                            autocomplete="new-password"
                            placeholder="Konfirmasi kata sandi baru"
                        />
                        <InputError :message="errors.password_confirmation" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            :disabled="processing"
                            data-test="update-password-button"
                        >
                            Simpan kata sandi
                        </Button>
                    </div>
                </Form>
            </div>

            <div
                v-if="canManageTwoFactor"
                class="my-card mt-6 flex flex-col gap-4 p-6"
            >
                <div>
                    <h2 class="text-lg font-bold text-[var(--my-neutral)]">
                        Autentikasi Dua Faktor
                    </h2>
                    <p class="text-sm text-[var(--my-muted)]">
                        Kelola pengaturan autentikasi dua faktor Anda
                    </p>
                </div>

                <div
                    v-if="!twoFactorEnabled"
                    class="flex flex-col items-start justify-start space-y-4"
                >
                    <p class="text-sm text-[var(--my-muted)]">
                        Saat autentikasi dua faktor aktif, Anda akan diminta
                        memasukkan kode PIN saat login. Kode PIN dapat diambil
                        dari aplikasi pendukung TOTP di ponsel Anda.
                    </p>

                    <div>
                        <Button
                            v-if="hasSetupData"
                            @click="showSetupModal = true"
                        >
                            <ShieldCheck />Lanjutkan pengaturan
                        </Button>
                        <Form
                            v-else
                            v-bind="enable.form()"
                            @success="showSetupModal = true"
                            #default="{ processing }"
                        >
                            <Button type="submit" :disabled="processing">
                                Aktifkan 2FA
                            </Button>
                        </Form>
                    </div>
                </div>

                <div
                    v-else
                    class="flex flex-col items-start justify-start space-y-4"
                >
                    <p class="text-sm text-[var(--my-muted)]">
                        Anda akan diminta memasukkan kode PIN acak saat login,
                        yang bisa diambil dari aplikasi pendukung TOTP di ponsel
                        Anda.
                    </p>

                    <div class="relative inline">
                        <Form v-bind="disable.form()" #default="{ processing }">
                            <Button
                                variant="destructive"
                                type="submit"
                                :disabled="processing"
                            >
                                Nonaktifkan 2FA
                            </Button>
                        </Form>
                    </div>

                    <TwoFactorRecoveryCodes />
                </div>

                <TwoFactorSetupModal
                    v-model:isOpen="showSetupModal"
                    :requiresConfirmation="requiresConfirmation"
                    :twoFactorEnabled="twoFactorEnabled"
                />
            </div>
        </div>
    </DashboardLayout>
</template>
