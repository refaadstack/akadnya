<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $content->bride_name }} & {{ $content->groom_name }} - Undangan Pernikahan</title>

    @php($faviconVersion = '20260906')
    <link rel="icon" href="{{ asset('favicon.ico?v='.$faviconVersion) }}" sizes="any">
    <link rel="icon" href="{{ asset('favicon.svg?v='.$faviconVersion) }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png?v='.$faviconVersion) }}">
    <meta name="theme-color" content="#141f1a">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Playfair Display', serif;
        }
        .font-arabic {
            font-family: 'Amiri', serif;
        }
        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d97706' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
</head>
<body class="bg-amber-50 bg-pattern">
    
    <!-- Background Music -->
    @if($content->music_url)
    <audio id="bgMusic" loop>
        <source src="{{ $content->music_url }}" type="audio/mpeg">
    </audio>
    
    <!-- Music Control Button -->
    <button id="musicToggle" class="fixed bottom-6 right-6 z-50 bg-amber-800 text-white p-4 rounded-full shadow-lg hover:bg-amber-900 transition">
        <svg id="iconPlay" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
        </svg>
        <svg id="iconPause" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" clip-rule="evenodd" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
        </svg>
    </button>
    @endif

    <!-- COVER SECTION -->
    <section id="cover" class="relative min-h-screen flex items-center justify-center bg-cover bg-center" style="background-image: url('{{ $content->cover_photo_url ?? '' }}');">
        <div class="absolute inset-0 bg-black bg-opacity-40"></div>
        <div class="relative z-10 text-center text-white px-4">
            @if($guestName)
            <p class="text-lg mb-4">Kepada Yth.</p>
            <h3 class="text-2xl font-bold mb-8">{{ $guestName }}</h3>
            @endif
            
            <h1 class="text-5xl md:text-7xl font-bold mb-4">{{ $content->bride_name }}</h1>
            <p class="text-3xl mb-4">&</p>
            <h1 class="text-5xl md:text-7xl font-bold mb-8">{{ $content->groom_name }}</h1>
            
            @if($content->akad_datetime)
            <p class="text-xl mb-8">{{ \Carbon\Carbon::parse($content->akad_datetime)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</p>
            @endif
            
            <button onclick="document.getElementById('opening').scrollIntoView({behavior: 'smooth'})" class="bg-amber-700 hover:bg-amber-800 text-white px-8 py-4 rounded-full text-lg transition">
                Buka Undangan
            </button>
        </div>
    </section>

    <!-- OPENING SECTION -->
    <section id="opening" class="py-20 px-4 bg-white">
        <div class="max-w-3xl mx-auto text-center">
            <p class="text-3xl font-arabic text-amber-900 mb-6">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</p>
            <p class="text-lg text-amber-800 mb-6">Assalamu'alaikum Warahmatullahi Wabarakatuh</p>
            
            <p class="text-amber-800 leading-relaxed mb-8">
                Dengan memohon rahmat dan ridho Allah SWT, kami bermaksud menyelenggarakan pernikahan putra-putri kami.
            </p>
            
            <div class="bg-amber-50 rounded-lg p-8 border-2 border-amber-200">
                <p class="text-2xl font-arabic text-amber-900 mb-4 leading-loose">
                    وَمِنْ ءَايَٰتِهِۦٓ أَنْ خَلَقَ لَكُم مِّنْ أَنفُسِكُمْ أَزْوَٰجًا لِّتَسْكُنُوٓا۟ إِلَيْهَا
                </p>
                <p class="text-sm text-amber-700 italic">
                    "Dan di antara tanda-tanda (kebesaran)-Nya ialah Dia menciptakan pasangan-pasangan untukmu dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya."
                </p>
                <p class="text-xs text-amber-600 mt-2 font-semibold">(QS. Ar-Rum: 21)</p>
            </div>
            
            @if($content->special_message)
            <p class="text-amber-800 mt-8 italic">{{ $content->special_message }}</p>
            @endif
        </div>
    </section>

    <!-- BRIDE & GROOM SECTION -->
    <section id="bride-groom" class="py-20 px-4 bg-gradient-to-b from-white to-amber-50">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-4xl font-bold text-center text-amber-900 mb-12">Mempelai</h2>
            
            <div class="grid md:grid-cols-2 gap-12">
                <!-- Bride -->
                <div class="text-center">
                    @if($content->bride_photo_url)
                    <div class="w-64 h-64 mx-auto mb-6 rounded-full overflow-hidden border-4 border-amber-300 shadow-lg">
                        <img src="{{ $content->bride_photo_url }}" alt="{{ $content->bride_name }}" class="w-full h-full object-cover">
                    </div>
                    @endif
                    
                    <h3 class="text-3xl font-bold text-amber-900 mb-2">{{ $content->bride_name }}</h3>
                    <p class="text-amber-700 mb-4">Putri dari:</p>
                    @if($content->bride_father)
                    <p class="text-amber-800">Bapak {{ $content->bride_father }}</p>
                    @endif
                    @if($content->bride_mother)
                    <p class="text-amber-800">Ibu {{ $content->bride_mother }}</p>
                    @endif
                    
                    @if($content->bride_instagram)
                    <a href="https://instagram.com/{{ $content->bride_instagram }}" target="_blank" class="inline-block mt-4 text-amber-700 hover:text-amber-900">
                        <svg class="w-6 h-6 inline" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                        @{{ $content->bride_instagram }}
                    </a>
                    @endif
                </div>
                
                <!-- Groom -->
                <div class="text-center">
                    @if($content->groom_photo_url)
                    <div class="w-64 h-64 mx-auto mb-6 rounded-full overflow-hidden border-4 border-amber-300 shadow-lg">
                        <img src="{{ $content->groom_photo_url }}" alt="{{ $content->groom_name }}" class="w-full h-full object-cover">
                    </div>
                    @endif
                    
                    <h3 class="text-3xl font-bold text-amber-900 mb-2">{{ $content->groom_name }}</h3>
                    <p class="text-amber-700 mb-4">Putra dari:</p>
                    @if($content->groom_father)
                    <p class="text-amber-800">Bapak {{ $content->groom_father }}</p>
                    @endif
                    @if($content->groom_mother)
                    <p class="text-amber-800">Ibu {{ $content->groom_mother }}</p>
                    @endif
                    
                    @if($content->groom_instagram)
                    <a href="https://instagram.com/{{ $content->groom_instagram }}" target="_blank" class="inline-block mt-4 text-amber-700 hover:text-amber-900">
                        <svg class="w-6 h-6 inline" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                        @{{ $content->groom_instagram }}
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- Continue with other sections... -->
    <!-- Due to length, I'll create this as a Blade template that can be extended -->

    <script>
        // Music player
        const music = document.getElementById('bgMusic');
        const musicToggle = document.getElementById('musicToggle');
        const iconPlay = document.getElementById('iconPlay');
        const iconPause = document.getElementById('iconPause');
        let isPlaying = false;

        if (music && musicToggle) {
            musicToggle.addEventListener('click', () => {
                if (isPlaying) {
                    music.pause();
                    iconPlay.classList.remove('hidden');
                    iconPause.classList.add('hidden');
                } else {
                    music.play();
                    iconPlay.classList.add('hidden');
                    iconPause.classList.remove('hidden');
                }
                isPlaying = !isPlaying;
            });

            // Auto-play on load
            music.play().then(() => {
                isPlaying = true;
                iconPlay.classList.add('hidden');
                iconPause.classList.remove('hidden');
            }).catch(() => {
                // Auto-play blocked
                isPlaying = false;
                iconPlay.classList.remove('hidden');
                iconPause.classList.add('hidden');
            });
        }
    </script>
</body>
</html>
