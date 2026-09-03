<script setup lang="ts">
interface InvitationContent {
    bride_name: string;
    groom_name: string;
}

defineProps<{
    content: InvitationContent;
}>();
</script>

<template>
    <section
        id="closing"
        class="bg-gradient-to-b from-amber-100 to-amber-200 px-4 py-20"
    >
        <div class="mx-auto max-w-3xl text-center">
            <!-- Decorative Top -->
            <div class="mb-8">
                <svg
                    class="mx-auto h-16 w-16 text-amber-700"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"
                    />
                </svg>
            </div>

            <!-- Thank You Message -->
            <h2 class="mb-6 font-serif text-4xl text-amber-900">
                Terima Kasih
            </h2>
            <div class="mx-auto mb-8 h-1 w-24 bg-amber-600"></div>

            <p class="mb-6 text-lg leading-relaxed text-amber-800">
                Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila
                Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu
                kepada kami.
            </p>

            <p class="mb-8 text-lg leading-relaxed text-amber-800">
                Atas kehadiran dan doa restunya, kami ucapkan terima kasih.
            </p>

            <!-- Quran Verse -->
            <div
                class="mb-8 rounded-lg border-2 border-amber-300 bg-white p-8 shadow-lg"
            >
                <p
                    class="font-arabic mb-4 text-2xl leading-loose text-amber-900"
                    style="font-family: 'Amiri', serif"
                >
                    وَمِنْ ءَايَٰتِهِۦٓ أَنْ خَلَقَ لَكُم مِّنْ أَنفُسِكُمْ
                    أَزْوَٰجًا لِّتَسْكُنُوٓا۟ إِلَيْهَا وَجَعَلَ بَيْنَكُم
                    مَّوَدَّةً وَرَحْمَةً ۚ إِنَّ فِى ذَٰلِكَ لَءَايَٰتٍ
                    لِّقَوْمٍ يَتَفَكَّرُونَ
                </p>
                <p class="text-sm text-amber-700 italic">
                    "Dan di antara tanda-tanda (kebesaran)-Nya ialah Dia
                    menciptakan pasangan-pasangan untukmu dari jenismu sendiri,
                    agar kamu cenderung dan merasa tenteram kepadanya, dan Dia
                    menjadikan di antaramu rasa kasih dan sayang. Sungguh, pada
                    yang demikian itu benar-benar terdapat tanda-tanda
                    (kebesaran Allah) bagi kaum yang berpikir."
                </p>
                <p class="mt-2 text-xs font-semibold text-amber-600">
                    (QS. Ar-Rum: 21)
                </p>
            </div>

            <!-- Couple Names -->
            <div class="mb-8">
                <p class="font-serif text-2xl text-amber-900">
                    {{ content.bride_name }} & {{ content.groom_name }}
                </p>
            </div>

            <!-- Wassalam -->
            <p class="mb-8 text-lg text-amber-800">
                وَالسَّلَامُ عَلَيْكُمْ وَرَحْمَةُ اللهِ وَبَرَكَاتُهُ
            </p>
            <p class="text-sm text-amber-700 italic">
                Wassalamu'alaikum Warahmatullahi Wabarakatuh
            </p>

            <!-- Decorative Bottom -->
            <div class="mt-12">
                <div class="flex items-center justify-center space-x-4">
                    <div class="h-0.5 w-16 bg-amber-600"></div>
                    <svg
                        class="h-8 w-8 text-amber-700"
                        fill="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"
                        />
                    </svg>
                    <div class="h-0.5 w-16 bg-amber-600"></div>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-12 border-t border-amber-300 pt-8">
                <p class="text-sm text-amber-600">
                    Made with ❤️ by Akadnya.com
                </p>
            </div>
        </div>
    </section>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap');
</style>
