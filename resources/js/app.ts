import { createInertiaApp } from '@inertiajs/vue3';
import { initializeTheme } from '@/composables/useAppearance';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        // Inertia v3: a page component's own `layout` option always wins;
        // this callback only applies to pages without one. Every non-auth
        // page ships its own chrome (DashboardLayout, AdminLayout or an
        // inline PublicNavbar), so only auth pages share a layout here.
        if (name.startsWith('auth/')) {
            return AuthLayout;
        }

        return null;
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
