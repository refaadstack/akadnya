<div class="prose prose-sm max-w-none space-y-4">
    {{-- Mode 1: HTML Single File --}}
    <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg p-4">
        <h4 class="text-sm font-semibold text-green-900 mb-2 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Opsi 1: Upload HTML Satu File (Cara Paling Mudah)
        </h4>
        <p class="text-xs text-green-800 mb-2">Upload satu file <code class="font-mono font-semibold">.html</code> lengkap (CSS &amp; JavaScript ditulis langsung di dalam file, seperti contoh undangan single-page).</p>
        <ul class="text-xs text-green-800 space-y-1 list-disc list-inside">
            <li>File harus berupa dokumen HTML utuh dengan tag <code class="font-mono">&lt;html&gt;</code> atau <code class="font-mono">&lt;body&gt;</code>.</li>
            <li>Ukuran maksimal 2MB.</li>
            <li>Nama file menjadi <strong>slug template</strong> (contoh: <code class="font-mono">undangan-rustic.html</code> → slug <code class="font-mono">undangan-rustic</code>).</li>
            <li>Judul template otomatis diambil dari nama file (contoh: <code class="font-mono">undangan-rustic</code> → "Undangan Rustic").</li>
            <li>Gunakan variabel data undangan dengan syntax Blade <code class="font-mono">{{ $bride_name }}</code> agar konten terisi otomatis dari database.</li>
        </ul>
    </div>

    {{-- Mode 2: ZIP --}}
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4">
        <h4 class="text-sm font-semibold text-blue-900 mb-2 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Opsi 2: Upload ZIP (Struktur Lengkap)
        </h4>

        <div class="bg-white rounded border border-gray-200 p-3 font-mono text-xs">
            <div class="space-y-1">
                <div class="text-gray-900">📦 my-template.zip</div>
                <div class="ml-4 text-gray-700">├── 📄 <span class="text-red-600 font-semibold">template.json</span> <span class="text-gray-500">(required)</span></div>
                <div class="ml-4 text-gray-700">├── 📁 <span class="text-red-600 font-semibold">sections/</span> <span class="text-gray-500">(required)</span></div>
                <div class="ml-8 text-gray-600">├── opening.html</div>
                <div class="ml-8 text-gray-600">├── hero.html</div>
                <div class="ml-8 text-gray-600">└── ...</div>
                <div class="ml-4 text-gray-700">├── 📁 <span class="text-red-600 font-semibold">assets/</span> <span class="text-gray-500">(required)</span></div>
                <div class="ml-8 text-gray-600">├── <span class="text-red-600 font-semibold">style.css</span> <span class="text-gray-500">(required)</span></div>
                <div class="ml-8 text-gray-600">└── script.js <span class="text-gray-400">(optional)</span></div>
                <div class="ml-4 text-gray-700">└── 📁 ornaments/ <span class="text-gray-400">(optional)</span></div>
            </div>
        </div>
    </div>

    {{-- Rules umum --}}
    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
        <h4 class="text-sm font-semibold text-amber-800 mb-2">📋 Aturan Umum Sebelum Upload</h4>
        <ul class="text-xs text-amber-800 space-y-1 list-disc list-inside">
            <li>Wajib menggunakan variabel data contract dari sistem (contoh: <code class="font-mono">$bride_name</code>, <code class="font-mono">$groom_name</code>, <code class="font-mono">$akad_datetime_formatted</code>, <code class="font-mono">$gallery</code>, <code class="font-mono">$wishes</code>, <code class="font-mono">$love_stories</code>).</li>
            <li>Gunakan syntax Blade ({{ }} / @if / @foreach) — bukan PHP murni.</li>
            <li>Dilarang memuat resource dari server lain yang tidak aman; semua gambar dapat memakai URL eksternal.</li>
            <li>Form RSVP wajib menggunakan <code class="font-mono">name="attendance"</code> (nilai <code class="font-mono">yes</code>/<code class="font-mono">no</code>), <code class="font-mono">name="name"</code>, <code class="font-mono">name="pax_count"</code>, <code class="font-mono">name="message"</code>, dan POST ke <code class="font-mono">{{ $rsvp_action }}</code>.</li>
            <li>Countdown memakai <code class="font-mono">data-date="{{ $akad_datetime }}"</code> (format ISO).</li>
            <li>Tombol salin memakai atribut <code class="font-mono">data-copy="#id-elemen"</code>.</li>
            <li>Ukuran maksimal file: 50MB (ZIP) / 2MB (HTML).</li>
            <li>Template akan langsung tampil di katalog setelah upload — pastikan sudah siap dipublikasikan.</li>
        </ul>
    </div>
</div>
