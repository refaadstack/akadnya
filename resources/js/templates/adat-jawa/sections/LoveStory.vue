<script setup lang="ts">
defineProps<{
    content: any;
}>();
</script>

<template>
    <section v-if="content.love_story" class="bg-white px-4 py-20">
        <div class="container mx-auto max-w-4xl">
            <!-- Title -->
            <div class="mb-16 text-center">
                <h2
                    class="mb-4 font-serif text-4xl font-bold text-amber-800 md:text-5xl"
                >
                    Cerita Cinta Kami
                </h2>
                <div class="flex items-center justify-center">
                    <div class="h-px w-20 bg-amber-400"></div>
                    <svg
                        class="mx-4 h-8 w-8 text-amber-600"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    <div class="h-px w-20 bg-amber-400"></div>
                </div>
            </div>

            <!-- Story Content -->
            <div
                class="rounded-2xl border-4 border-amber-200 bg-gradient-to-br from-amber-50 to-white p-8 shadow-xl md:p-12"
            >
                <!-- Decorative Quote -->
                <div class="mb-6 text-amber-600">
                    <svg
                        class="h-12 w-12"
                        fill="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"
                        />
                    </svg>
                </div>

                <!-- Story Text -->
                <div class="prose prose-lg max-w-none">
                    <p
                        class="text-lg leading-relaxed whitespace-pre-line text-gray-700"
                    >
                        {{ content.love_story }}
                    </p>
                </div>

                <!-- Decorative Quote End -->
                <div class="mt-6 flex justify-end text-amber-600">
                    <svg
                        class="h-12 w-12 rotate-180 transform"
                        fill="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"
                        />
                    </svg>
                </div>

                <!-- Hearts Decoration -->
                <div class="mt-8 flex justify-center space-x-2">
                    <svg
                        class="h-6 w-6 text-amber-400"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    <svg
                        class="h-6 w-6 text-amber-500"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    <svg
                        class="h-6 w-6 text-amber-600"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </div>
            </div>
        </div>
    </section>
</template>
