<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PublicNavbar from '@/components/PublicNavbar.vue';
import PublicFooter from '@/components/PublicFooter.vue';

const page = usePage();
const supportEmail = computed(() => page.props.support.email);
const supportWhatsapp = computed(() => page.props.support.whatsapp);

interface Question {
    question: string;
    answer: string;
}

interface FaqCategory {
    category: string;
    icon: string;
    questions: Question[];
}

interface Meta {
    title: string;
    description: string;
    keywords: string;
}

const props = defineProps<{
    faqs: FaqCategory[];
    meta: Meta;
    canRegister?: boolean;
}>();

const searchQuery = ref('');
const activeCategory = ref<string | null>(null);
const expandedQuestions = ref<Set<string>>(new Set());

// Filter FAQs based on search query
const filteredFaqs = computed(() => {
    if (!searchQuery.value) {
        return props.faqs;
    }

    const query = searchQuery.value.toLowerCase();
    return props.faqs
        .map((category) => ({
            ...category,
            questions: category.questions.filter(
                (q) =>
                    q.question.toLowerCase().includes(query) ||
                    q.answer.toLowerCase().includes(query),
            ),
        }))
        .filter((category) => category.questions.length > 0);
});

// Get icon class based on icon name
const getIconClass = (icon: string): string => {
    const icons: Record<string, string> = {
        info: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'credit-card':
            'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
        palette:
            'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01',
        globe: 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        image: 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
        settings:
            'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
        share: 'M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z',
        tool: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
    };
    return icons[icon] || icons.info;
};

// Toggle question expansion
const toggleQuestion = (categoryIndex: number, questionIndex: number) => {
    const key = `${categoryIndex}-${questionIndex}`;
    if (expandedQuestions.value.has(key)) {
        expandedQuestions.value.delete(key);
    } else {
        expandedQuestions.value.add(key);
    }
};

// Check if question is expanded
const isExpanded = (categoryIndex: number, questionIndex: number): boolean => {
    return expandedQuestions.value.has(`${categoryIndex}-${questionIndex}`);
};

// Generate structured data for SEO (JSON-LD)
const structuredData = computed(() => {
    const mainEntity = props.faqs.flatMap((category) =>
        category.questions.map((q) => ({
            '@type': 'Question',
            name: q.question,
            acceptedAnswer: {
                '@type': 'Answer',
                text: q.answer,
            },
        })),
    );

    return {
        '@context': 'https://schema.org',
        '@type': 'FAQPage',
        mainEntity,
    };
});
</script>

<template>
    <div>
        <Head :title="meta.title">
            <meta name="description" :content="meta.description" />
            <meta name="keywords" :content="meta.keywords" />

            <!-- Open Graph / Facebook -->
            <meta property="og:type" content="website" />
            <meta property="og:title" :content="meta.title" />
            <meta property="og:description" :content="meta.description" />

            <!-- Twitter -->
            <meta property="twitter:card" content="summary_large_image" />
            <meta property="twitter:title" :content="meta.title" />
            <meta property="twitter:description" :content="meta.description" />

            <!-- Structured Data (JSON-LD) -->
            <script
                type="application/ld+json"
                v-html="JSON.stringify(structuredData)"
            ></script>
        </Head>

        <div class="my-page">
            <!-- Navbar -->
            <PublicNavbar :can-register="canRegister" current-page="faq" />

            <!-- Hero Section -->
            <section class="pt-32 pb-16 md:pb-24">
                <div class="my-container">
                    <div class="mx-auto max-w-4xl text-center">
                        <p class="my-label mb-4">Pusat Bantuan</p>
                        <h1 class="my-heading mb-6 text-4xl md:text-5xl">
                            Pertanyaan yang Sering Diajukan
                        </h1>
                        <p class="my-copy mb-8">
                            Temukan jawaban untuk pertanyaan umum tentang MyAkad
                        </p>

                        <!-- Search Box -->
                        <div class="relative mx-auto max-w-2xl">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari pertanyaan... (contoh: subdomain, template, RSVP)"
                                class="my-input w-full px-6 py-4 pr-12 text-lg"
                            />
                            <svg
                                class="absolute top-1/2 right-5 h-6 w-6 -translate-y-1/2 transform text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                        </div>

                        <!-- Search Results Count -->
                        <p
                            v-if="searchQuery"
                            class="mt-4 text-[var(--my-muted)]"
                        >
                            Ditemukan
                            {{
                                filteredFaqs.reduce(
                                    (sum, cat) => sum + cat.questions.length,
                                    0,
                                )
                            }}
                            pertanyaan
                        </p>
                    </div>
                </div>
            </section>

            <!-- FAQ Content -->
            <section class="pb-20">
                <div class="my-container">
                    <div class="mx-auto max-w-5xl">
                        <!-- Categories -->
                        <div
                            v-for="(category, categoryIndex) in filteredFaqs"
                            :key="categoryIndex"
                            class="mb-12"
                        >
                            <!-- Category Header -->
                            <div class="mb-6 flex items-center">
                                <div
                                    class="mr-4 flex h-12 w-12 items-center justify-center rounded-xl bg-[var(--my-primary)]"
                                >
                                    <svg
                                        class="h-6 w-6 text-white"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            :d="getIconClass(category.icon)"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="my-heading text-2xl">
                                        {{ category.category }}
                                    </h2>
                                    <p class="text-[var(--my-muted)]">
                                        {{
                                            category.questions.length
                                        }}
                                        pertanyaan
                                    </p>
                                </div>
                            </div>

                            <!-- Questions -->
                            <div class="space-y-4">
                                <div
                                    v-for="(
                                        question, questionIndex
                                    ) in category.questions"
                                    :key="questionIndex"
                                    class="my-card overflow-hidden transition hover:border-[var(--my-primary)]/40"
                                >
                                    <button
                                        @click="
                                            toggleQuestion(
                                                categoryIndex,
                                                questionIndex,
                                            )
                                        "
                                        class="flex w-full items-center justify-between px-6 py-5 text-left transition hover:bg-[var(--my-surface-soft)]/70"
                                    >
                                        <h3
                                            class="pr-4 text-lg font-semibold text-[var(--my-neutral)]"
                                        >
                                            {{ question.question }}
                                        </h3>
                                        <svg
                                            class="h-6 w-6 flex-shrink-0 text-[var(--my-primary)] transition-transform"
                                            :class="{
                                                'rotate-180': isExpanded(
                                                    categoryIndex,
                                                    questionIndex,
                                                ),
                                            }"
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

                                    <div
                                        v-show="
                                            isExpanded(
                                                categoryIndex,
                                                questionIndex,
                                            )
                                        "
                                        class="border-t border-[var(--my-border)] px-6 pb-5 leading-relaxed text-[var(--my-muted)]"
                                    >
                                        <p class="pt-4">
                                            {{ question.answer }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- No Results -->
                        <div
                            v-if="filteredFaqs.length === 0"
                            class="py-12 text-center"
                        >
                            <svg
                                class="mx-auto mb-4 h-24 w-24 text-gray-300"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            <h3 class="my-heading mb-2 text-xl">
                                Tidak ada hasil ditemukan
                            </h3>
                            <p class="mb-6 text-[var(--my-muted)]">
                                Coba kata kunci lain atau hubungi support kami
                            </p>
                            <a
                                :href="`mailto:${supportEmail}`"
                                class="my-btn-primary inline-flex items-center px-6"
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
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                    />
                                </svg>
                                Hubungi Support
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA Section -->
            <section class="bg-[var(--my-primary)] py-16">
                <div class="my-container text-center">
                    <h2 class="my-heading mb-4 text-3xl text-white md:text-4xl">
                        Masih Ada Pertanyaan?
                    </h2>
                    <p class="mx-auto mb-8 max-w-2xl text-xl text-white/80">
                        Tim support kami siap membantu Anda. Hubungi kami
                        melalui email atau WhatsApp.
                    </p>
                    <div
                        class="flex flex-col items-center justify-center gap-4 sm:flex-row"
                    >
                        <a
                            :href="`mailto:${supportEmail}`"
                            class="inline-flex items-center rounded-lg bg-white px-8 py-4 font-semibold text-[var(--my-primary)] transition hover:shadow-xl"
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
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                />
                            </svg>
                            Email Support
                        </a>
                        <a
                            :href="`https://wa.me/${supportWhatsapp}`"
                            target="_blank"
                            class="inline-flex items-center rounded-lg bg-[var(--my-neutral)] px-8 py-4 font-semibold text-white transition hover:shadow-xl"
                        >
                            <svg
                                class="mr-2 h-5 w-5"
                                fill="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"
                                />
                            </svg>
                            WhatsApp
                        </a>
                    </div>
                </div>
            </section>

            <!-- Footer -->
            <PublicFooter />
        </div>
    </div>
</template>
