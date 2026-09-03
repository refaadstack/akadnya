<script setup lang="ts">
defineProps<{
    content: any;
}>();
</script>

<template>
    <section class="bg-gradient-to-b from-amber-50 to-white px-4 py-20">
        <div class="container mx-auto max-w-4xl text-center">
            <!-- Bismillah -->
            <div class="mb-12">
                <p class="font-arabic mb-4 text-4xl text-amber-800 md:text-5xl">
                    بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ
                </p>
                <p class="text-lg text-amber-700 italic">
                    Bismillahirrahmanirrahim
                </p>
            </div>

            <!-- Ornamental Divider -->
            <div class="mb-8 flex items-center justify-center">
                <div class="h-px w-20 bg-amber-400"></div>
                <svg
                    class="mx-4 h-10 w-10 text-amber-600"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                >
                    <path
                        fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z"
                        clip-rule="evenodd"
                    />
                </svg>
                <div class="h-px w-20 bg-amber-400"></div>
            </div>

            <!-- Opening Text -->
            <div class="prose prose-lg mx-auto text-gray-700">
                <p class="mb-6 text-xl leading-relaxed">
                    Assalamu'alaikum Warahmatullahi Wabarakatuh
                </p>

                <p class="mb-6 text-lg leading-relaxed">
                    Dengan memohon rahmat dan ridho Allah Subhanahu Wa Ta'ala,
                    kami bermaksud mengundang Bapak/Ibu/Saudara/i untuk
                    menghadiri acara pernikahan kami:
                </p>

                <!-- Quran Verse -->
                <div
                    class="my-8 rounded-r-lg border-l-4 border-amber-600 bg-amber-100 p-6"
                >
                    <p
                        class="font-arabic mb-3 text-2xl leading-loose text-amber-900"
                    >
                        وَمِنْ ءَايَٰتِهِۦٓ أَنْ خَلَقَ لَكُم مِّنْ أَنفُسِكُمْ
                        أَزْوَٰجًا لِّتَسْكُنُوٓا۟ إِلَيْهَا وَجَعَلَ بَيْنَكُم
                        مَّوَدَّةً وَرَحْمَةً ۚ إِنَّ فِى ذَٰلِكَ لَءَايَٰتٍ
                        لِّقَوْمٍ يَتَفَكَّرُونَ
                    </p>
                    <p class="text-sm text-amber-800 italic">
                        "Dan di antara tanda-tanda (kebesaran)-Nya ialah Dia
                        menciptakan pasangan-pasangan untukmu dari jenismu
                        sendiri, agar kamu cenderung dan merasa tenteram
                        kepadanya, dan Dia menjadikan di antaramu rasa kasih dan
                        sayang. Sungguh, pada yang demikian itu benar-benar
                        terdapat tanda-tanda (kebesaran Allah) bagi kaum yang
                        berpikir."
                    </p>
                    <p class="mt-2 text-xs font-semibold text-amber-700">
                        (QS. Ar-Rum: 21)
                    </p>
                </div>

                <p
                    v-if="content.special_message"
                    class="mt-8 text-lg leading-relaxed text-gray-600 italic"
                >
                    {{ content.special_message }}
                </p>
            </div>
        </div>
    </section>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap');

.font-arabic {
    font-family: 'Amiri', serif;
}
</style>
