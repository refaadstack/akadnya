<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const showUserMenu = ref(false);

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="my-page min-h-screen">
        <!-- Navigation -->
        <nav
            class="sticky top-0 z-40 border-b border-[var(--my-border)] bg-[var(--my-background)]/86 backdrop-blur-md"
        >
            <div class="container mx-auto px-4">
                <div class="flex h-16 items-center justify-between">
                    <!-- Logo -->
                    <Link
                        href="/admin/dashboard"
                        class="flex items-center space-x-2"
                    >
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-[var(--my-primary)]"
                        >
                            <span class="text-xl font-bold text-white">A</span>
                        </div>
                        <div>
                            <span
                                class="font-display text-xl font-bold text-[var(--my-primary)]"
                                >Admin Panel</span
                            >
                            <span class="block text-xs text-[var(--my-muted)]"
                                >Akadnya.com</span
                            >
                        </div>
                    </Link>

                    <!-- Navigation Links -->
                    <div class="hidden items-center space-x-6 md:flex">
                        <Link
                            href="/admin/dashboard"
                            class="flex items-center font-medium text-gray-700 transition hover:text-red-600"
                            :class="{
                                'text-red-600':
                                    $page.url.startsWith('/admin/dashboard'),
                            }"
                        >
                            <svg
                                class="mr-1 h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                                />
                            </svg>
                            Dashboard
                        </Link>

                        <Link
                            href="/admin/templates"
                            class="flex items-center font-medium text-gray-700 transition hover:text-red-600"
                            :class="{
                                'text-red-600':
                                    $page.url.startsWith('/admin/templates'),
                            }"
                        >
                            <svg
                                class="mr-1 h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"
                                />
                            </svg>
                            Templates
                        </Link>

                        <Link
                            href="/dashboard"
                            class="flex items-center font-medium text-gray-700 transition hover:text-blue-600"
                        >
                            <svg
                                class="mr-1 h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                />
                            </svg>
                            User View
                        </Link>
                    </div>

                    <!-- User Menu -->
                    <div class="relative">
                        <button
                            @click="showUserMenu = !showUserMenu"
                            class="flex items-center space-x-3 focus:outline-none"
                        >
                            <div class="hidden text-right md:block">
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $page.props.auth?.user?.name }}
                                </p>
                                <p class="text-xs font-medium text-red-600">
                                    Admin
                                </p>
                            </div>
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-red-500 to-orange-600"
                            >
                                <span class="text-sm font-semibold text-white">
                                    {{
                                        $page.props.auth?.user?.name
                                            ?.charAt(0)
                                            .toUpperCase() || 'A'
                                    }}
                                </span>
                            </div>
                            <svg
                                class="h-4 w-4 text-gray-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 9l-7 7-7-7"
                                />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div
                            v-show="showUserMenu"
                            @click="showUserMenu = false"
                            class="absolute right-0 z-50 mt-2 w-48 rounded-lg border border-gray-200 bg-white py-2 shadow-lg"
                        >
                            <div class="border-b border-gray-100 px-4 py-2">
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $page.props.auth?.user?.name }}
                                </p>
                                <p class="text-xs text-gray-600">
                                    {{ $page.props.auth?.user?.email }}
                                </p>
                            </div>

                            <Link
                                href="/admin/dashboard"
                                class="block px-4 py-2 text-sm text-gray-700 transition hover:bg-gray-100"
                            >
                                <div class="flex items-center">
                                    <svg
                                        class="mr-2 h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                                        />
                                    </svg>
                                    Admin Dashboard
                                </div>
                            </Link>

                            <Link
                                href="/dashboard"
                                class="block px-4 py-2 text-sm text-gray-700 transition hover:bg-gray-100"
                            >
                                <div class="flex items-center">
                                    <svg
                                        class="mr-2 h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                        />
                                    </svg>
                                    User Dashboard
                                </div>
                            </Link>

                            <Link
                                href="/settings/profile"
                                class="block px-4 py-2 text-sm text-gray-700 transition hover:bg-gray-100"
                            >
                                <div class="flex items-center">
                                    <svg
                                        class="mr-2 h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                        />
                                    </svg>
                                    Settings
                                </div>
                            </Link>

                            <div class="mt-2 border-t border-gray-100 pt-2">
                                <button
                                    @click="logout"
                                    class="block w-full px-4 py-2 text-left text-sm text-red-600 transition hover:bg-red-50"
                                >
                                    <div class="flex items-center">
                                        <svg
                                            class="mr-2 h-4 w-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                            />
                                        </svg>
                                        Logout
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="mt-12 border-t border-[var(--my-border)] bg-white/60">
            <div class="container mx-auto px-4 py-6">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-600">
                        © 2026 Akadnya.com Admin Panel. All rights reserved.
                    </p>
                    <div
                        class="flex items-center space-x-4 text-sm text-gray-600"
                    >
                        <a href="#" class="transition hover:text-red-600"
                            >Documentation</a
                        >
                        <a href="#" class="transition hover:text-red-600"
                            >Support</a
                        >
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
