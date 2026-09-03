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
            class="border-b border-[var(--my-border)] bg-[var(--my-background)]/86 backdrop-blur-md"
        >
            <div class="container mx-auto px-4">
                <div class="flex h-16 items-center justify-between">
                    <!-- Logo -->
                    <Link href="/dashboard" class="flex items-center space-x-2">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-[var(--my-primary)]"
                        >
                            <span class="text-xl font-bold text-white">M</span>
                        </div>
                        <span
                            class="font-display text-2xl font-bold text-[var(--my-primary)]"
                        >
                            Akadnya.com
                        </span>
                    </Link>

                    <!-- Navigation Links -->
                    <div class="hidden items-center space-x-6 md:flex">
                        <Link
                            href="/dashboard"
                            class="font-medium text-gray-700 transition hover:text-pink-600"
                            :class="{
                                'text-pink-600': $page.url === '/dashboard',
                            }"
                        >
                            Dashboard
                        </Link>
                        <Link
                            href="/dashboard/editor"
                            class="font-medium text-gray-700 transition hover:text-pink-600"
                            :class="{
                                'text-pink-600':
                                    $page.url.startsWith('/dashboard/editor'),
                            }"
                        >
                            Editor
                        </Link>
                        <Link
                            href="/dashboard/customize"
                            class="font-medium text-gray-700 transition hover:text-pink-600"
                            :class="{
                                'text-pink-600': $page.url.startsWith(
                                    '/dashboard/customize',
                                ),
                            }"
                        >
                            Kustomisasi
                        </Link>
                        <Link
                            href="/dashboard/gallery"
                            class="font-medium text-gray-700 transition hover:text-pink-600"
                            :class="{
                                'text-pink-600':
                                    $page.url.startsWith('/dashboard/gallery'),
                            }"
                        >
                            Galeri
                        </Link>
                        <Link
                            href="/dashboard/guests"
                            class="font-medium text-gray-700 transition hover:text-pink-600"
                            :class="{
                                'text-pink-600':
                                    $page.url.startsWith('/dashboard/guests'),
                            }"
                        >
                            Tamu
                        </Link>
                        <Link
                            href="/dashboard/rsvp"
                            class="font-medium text-gray-700 transition hover:text-pink-600"
                            :class="{
                                'text-pink-600':
                                    $page.url.startsWith('/dashboard/rsvp'),
                            }"
                        >
                            RSVP
                        </Link>
                    </div>

                    <!-- User Menu -->
                    <div class="relative">
                        <button
                            @click="showUserMenu = !showUserMenu"
                            class="flex items-center space-x-3 focus:outline-none"
                        >
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-pink-500 to-purple-600"
                            >
                                <span class="text-sm font-semibold text-white">
                                    {{
                                        $page.props.auth?.user?.name
                                            ?.charAt(0)
                                            .toUpperCase() || 'U'
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
                            class="absolute right-0 z-50 mt-2 w-48 rounded-lg bg-white py-2 shadow-lg"
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
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                        />
                                    </svg>
                                    Profil
                                </div>
                            </Link>

                            <Link
                                href="/settings/security"
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
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                        />
                                    </svg>
                                    Keamanan
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
                                        Keluar
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
    </div>
</template>
