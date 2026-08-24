import { createInertiaApp } from '@inertiajs/vue3';
import { initializeTheme } from '@/composables/useAppearance';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        // Inertia v3: a page component's `layout` option only wins when it
        // is truthy or an explicit function — `layout: undefined` still
        // falls through to this callback. Pages that ship their own chrome
        // (DashboardLayout, AdminLayout or an inline PublicNavbar) must
        // therefore declare `layout: () => null`. Only auth pages that rely
        // on shared chrome (ConfirmPassword, TwoFactorChallenge) are
        // wrapped here.
        if (
            name === 'auth/ConfirmPassword' ||
            name === 'auth/TwoFactorChallenge'
        ) {
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
