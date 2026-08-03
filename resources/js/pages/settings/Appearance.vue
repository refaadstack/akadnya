<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';

defineOptions({
    layout: undefined,
});
</script>

<template>
    <DashboardLayout>
        <Head title="Tampilan" />

        <div class="container mx-auto max-w-3xl px-4 py-8">
            <h1
                class="font-display mb-6 text-2xl font-bold text-[var(--my-neutral)]"
            >
                Tampilan
            </h1>

            <div class="my-card flex flex-col gap-6 p-6">
                <div>
                    <h2 class="text-lg font-bold text-[var(--my-neutral)]">
                        Pengaturan Tampilan
                    </h2>
                    <p class="text-sm text-[var(--my-muted)]">
                        Atur tampilan akun Anda
                    </p>
                </div>
                <AppearanceTabs />
            </div>
        </div>
    </DashboardLayout>
</template>
