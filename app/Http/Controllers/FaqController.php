<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    /**
     * Show FAQ page
     */
    public function index(): Response
    {
        $faqs = $this->getFaqData();

        return Inertia::render('Faq', [
            'faqs' => $faqs,
            'meta' => [
                'title' => 'FAQ - Pertanyaan yang Sering Diajukan | Akadnya.com',
                'description' => 'Temukan jawaban untuk pertanyaan umum tentang Akadnya.com - platform undangan digital pernikahan online. Panduan lengkap tentang cara membuat, mengelola, dan membagikan undangan digital.',
                'keywords' => 'faq akadnya, pertanyaan undangan digital, cara membuat undangan online, panduan akadnya, bantuan undangan digital, tutorial undangan pernikahan online',
            ],
            'canRegister' => Route::has('register'),
        ]);
    }

    /**
     * Get FAQ data organized by categories
     */
    private function getFaqData(): array
    {
        return [
            [
                'category' => 'Umum',
                'icon' => 'info',
                'questions' => [
                    [
                        'question' => 'Apa itu Akadnya.com?',
                        'answer' => 'Akadnya.com adalah platform undangan digital pernikahan online yang memungkinkan Anda membuat undangan pernikahan yang indah, modern, dan mudah dibagikan. Dengan Akadnya.com, Anda bisa membuat undangan digital dengan berbagai template menarik, mengelola daftar tamu, menerima konfirmasi kehadiran (RSVP), dan bahkan menerima amplop digital.',
                    ],
                    [
                        'question' => 'Apakah undangan bisa diakses tanpa internet?',
                        'answer' => 'Tidak, undangan digital Akadnya.com memerlukan koneksi internet untuk diakses. Namun, undangan kami dioptimalkan untuk loading cepat dan bisa diakses dari berbagai perangkat (smartphone, tablet, laptop, desktop).',
                    ],
                    [
                        'question' => 'Berapa lama undangan aktif?',
                        'answer' => 'Undangan Anda selalu aktif tanpa batas waktu selama layanan Akadnya.com berjalan. Tidak ada masa kedaluwarsa, jadi Anda tidak perlu perpanjang paket — undangan tetap bisa diakses oleh tamu kapan saja.',
                    ],
                    [
                        'question' => 'Apakah ada batasan jumlah tamu?',
                        'answer' => 'Tidak ada batasan! Semua paket Akadnya.com mendukung unlimited guests. Anda bisa mengundang sebanyak mungkin tamu tanpa biaya tambahan.',
                    ],
                    [
                        'question' => 'Apakah tamu perlu login untuk melihat undangan?',
                        'answer' => 'Tidak perlu! Tamu bisa langsung membuka undangan melalui link yang Anda bagikan tanpa perlu registrasi atau login. Ini membuat akses undangan menjadi sangat mudah dan praktis.',
                    ],
                ],
            ],
            [
                'category' => 'Paket & Harga',
                'icon' => 'credit-card',
                'questions' => [
                    [
                        'question' => 'Apa saja paket yang tersedia?',
                        'answer' => 'Akadnya.com menyediakan beberapa paket: Basic, Premium, dan Lifetime. Semua paket memiliki fitur yang sama dan undangan akan selalu aktif tanpa batas waktu selama layanan Akadnya.com berjalan — paket hanya menentukan apa yang Anda bayar di awal, bukan berapa lama undangan bisa diakses.',
                    ],
                    [
                        'question' => 'Bagaimana cara pembayaran?',
                        'answer' => 'Kami menerima pembayaran melalui berbagai metode: Transfer Bank (BCA, Mandiri, BNI, BRI), E-wallet (GoPay, OVO, DANA), dan QRIS. Pembayaran diproses melalui payment gateway Midtrans yang aman dan terpercaya.',
                    ],
                    [
                        'question' => 'Apakah bisa refund jika tidak jadi pakai?',
                        'answer' => 'Karena ini produk digital, pembelian dianggap final setelah checkout. Refund hanya bisa dilakukan dalam kondisi yang wajar, misalnya karena kesalahan dari pihak Akadnya.com atau kondisi khusus lain yang memang layak. Silakan hubungi customer support kami untuk proses refund.',
                    ],
                    [
                        'question' => 'Apakah ada biaya tambahan?',
                        'answer' => 'Tidak ada biaya tersembunyi! Harga yang tertera sudah termasuk semua fitur. Undangan Anda tetap aktif tanpa batas waktu, jadi tidak ada biaya perpanjangan.',
                    ],
                ],
            ],
            [
                'category' => 'Template & Desain',
                'icon' => 'palette',
                'questions' => [
                    [
                        'question' => 'Berapa banyak template yang tersedia?',
                        'answer' => 'Kami memiliki berbagai template dengan tema yang berbeda: Modern, Tradisional (Jawa, Sunda, Betawi, Minang), Elegant, Minimalis, dan lainnya. Template terus ditambah setiap bulannya.',
                    ],
                    [
                        'question' => 'Bisa ganti template setelah beli?',
                        'answer' => 'Sayangnya tidak bisa. Template yang sudah dipilih tidak bisa diganti karena setiap template memiliki struktur dan konfigurasi yang berbeda. Pastikan Anda memilih template yang sesuai sebelum checkout.',
                    ],
                    [
                        'question' => 'Apakah bisa kustomisasi warna dan font?',
                        'answer' => 'Setiap template sudah memiliki skema warna dan font yang dirancang secara profesional. Saat ini kustomisasi warna dan font belum tersedia, namun Anda bisa mengatur urutan section dan menampilkan/menyembunyikan bagian tertentu.',
                    ],
                    [
                        'question' => 'Apakah template responsive di semua device?',
                        'answer' => 'Ya! Semua template Akadnya.com dirancang responsive dan mobile-first. Undangan akan tampil sempurna di smartphone, tablet, laptop, dan desktop dengan berbagai ukuran layar.',
                    ],
                ],
            ],
            [
                'category' => 'Subdomain & Domain',
                'icon' => 'globe',
                'questions' => [
                    [
                        'question' => 'Apa itu subdomain?',
                        'answer' => 'Subdomain adalah alamat unik untuk undangan Anda di Akadnya.com. Contoh: siti-budi-2024.akadnya.com. Subdomain ini yang akan Anda bagikan ke tamu undangan.',
                    ],
                    [
                        'question' => 'Kenapa subdomain otomatis jadi nama saya?',
                        'answer' => 'Sistem otomatis membuat subdomain dari nama mempelai atau nama user untuk kemudahan dan personalisasi. Namun, Anda bisa mengubahnya kapan saja di menu Settings dengan subdomain yang Anda inginkan.',
                    ],
                    [
                        'question' => 'Bagaimana cara mengubah subdomain?',
                        'answer' => 'Buka Dashboard → Settings → bagian Subdomain. Ketik subdomain baru yang Anda inginkan (minimal 3 karakter, hanya huruf kecil, angka, dan tanda hubung), lalu klik Simpan Subdomain. Atau klik Generate Random untuk mendapatkan subdomain acak.',
                    ],
                    [
                        'question' => 'Bisa ganti subdomain setelah publikasi?',
                        'answer' => 'Bisa, tapi URL lama tidak akan berfungsi lagi. Jika Anda sudah membagikan URL ke tamu, sebaiknya jangan ganti subdomain atau informasikan URL baru ke semua tamu.',
                    ],
                    [
                        'question' => 'Apa itu custom domain?',
                        'answer' => 'Custom domain memungkinkan Anda menggunakan domain sendiri untuk undangan (contoh: undangan.example.com). Fitur ini tersedia untuk paket Premium dan Lifetime. Anda perlu setup CNAME record di DNS provider domain Anda.',
                    ],
                    [
                        'question' => 'Bagaimana cara setup custom domain?',
                        'answer' => 'Beli domain dari provider (Niagahoster, Dewaweb, dll), login ke DNS Management, buat CNAME record yang mengarah ke akadnya.com, tunggu propagasi DNS (5-30 menit), lalu masukkan domain di Settings Akadnya.com. Panduan lengkap tersedia di halaman Settings.',
                    ],
                ],
            ],
            [
                'category' => 'Konten & Media',
                'icon' => 'image',
                'questions' => [
                    [
                        'question' => 'Format foto apa yang didukung?',
                        'answer' => 'Akadnya.com mendukung format JPG, PNG, dan WebP. Untuk hasil terbaik, gunakan foto dengan resolusi tinggi dan ukuran file tidak lebih dari 5MB per foto.',
                    ],
                    [
                        'question' => 'Berapa ukuran maksimal foto?',
                        'answer' => 'Foto cover dan foto mempelai: maksimal 5MB. Foto galeri: maksimal 5MB per foto. QRIS: maksimal 2MB. Pastikan foto sudah dikompres untuk loading lebih cepat.',
                    ],
                    [
                        'question' => 'Apakah bisa upload video?',
                        'answer' => 'Saat ini Akadnya.com belum mendukung upload video langsung. Namun, Anda bisa embed video dari YouTube atau Vimeo dengan menambahkan link di bagian konten.',
                    ],
                    [
                        'question' => 'Format musik apa yang didukung?',
                        'answer' => 'Akadnya.com mendukung format MP3 dan WAV dengan ukuran maksimal 10MB. Pilih musik instrumental atau lagu romantis yang sesuai dengan tema pernikahan Anda.',
                    ],
                    [
                        'question' => 'Berapa banyak foto yang bisa diupload ke galeri?',
                        'answer' => 'Tidak ada batasan jumlah foto galeri. Namun, kami merekomendasikan 6-12 foto untuk performa loading yang optimal. Pilih foto terbaik yang mewakili momen spesial Anda.',
                    ],
                ],
            ],
            [
                'category' => 'Fitur & Fungsionalitas',
                'icon' => 'settings',
                'questions' => [
                    [
                        'question' => 'Apa itu RSVP?',
                        'answer' => 'RSVP (Répondez s\'il vous plaît) adalah fitur konfirmasi kehadiran tamu. Tamu bisa mengisi form RSVP di undangan untuk mengkonfirmasi apakah mereka akan hadir atau tidak, berapa jumlah orang yang hadir (pax), dan meninggalkan pesan.',
                    ],
                    [
                        'question' => 'Bagaimana cara melihat data RSVP?',
                        'answer' => 'Buka Dashboard → RSVP untuk melihat semua konfirmasi kehadiran. Anda bisa melihat statistik (jumlah hadir/tidak hadir), daftar tamu yang sudah konfirmasi, dan export data ke Excel/CSV.',
                    ],
                    [
                        'question' => 'Apa itu amplop digital?',
                        'answer' => 'Amplop digital adalah fitur untuk menerima hadiah pernikahan secara online. Anda bisa menampilkan informasi rekening bank, QRIS, atau nomor e-wallet (GoPay, OVO, DANA) di undangan. Tamu bisa langsung transfer hadiah tanpa perlu membawa amplop fisik.',
                    ],
                    [
                        'question' => 'Apakah bisa tracking berapa orang yang buka undangan?',
                        'answer' => 'Ya! Dashboard menampilkan jumlah views (berapa kali undangan dibuka). Ini membantu Anda memantau seberapa banyak tamu yang sudah melihat undangan.',
                    ],
                    [
                        'question' => 'Apakah bisa kirim undangan otomatis via WhatsApp?',
                        'answer' => 'Saat ini fitur kirim otomatis belum tersedia. Anda perlu copy link undangan dan kirim manual ke tamu via WhatsApp, email, atau media sosial. Kami menyediakan template pesan yang bisa Anda gunakan.',
                    ],
                    [
                        'question' => 'Apakah ada fitur countdown?',
                        'answer' => 'Ya! Semua template memiliki fitur countdown yang menampilkan hitung mundur hari, jam, menit, dan detik menuju hari pernikahan. Countdown akan otomatis berhenti saat waktu acara tiba.',
                    ],
                ],
            ],
            [
                'category' => 'Publikasi & Sharing',
                'icon' => 'share',
                'questions' => [
                    [
                        'question' => 'Bagaimana cara mempublikasikan undangan?',
                        'answer' => 'Setelah konten lengkap, buka Dashboard → Settings, pastikan data wajib sudah terisi (nama mempelai, tanggal & tempat akad), lalu klik tombol Publikasikan. Undangan akan langsung bisa diakses publik.',
                    ],
                    [
                        'question' => 'Apakah bisa unpublish undangan?',
                        'answer' => 'Bisa! Jika ada perubahan atau kesalahan, Anda bisa unpublish undangan di Settings. Undangan tidak bisa diakses sementara. Setelah edit selesai, publikasikan kembali.',
                    ],
                    [
                        'question' => 'Bagaimana cara membagikan undangan ke tamu?',
                        'answer' => 'Copy URL undangan dari Dashboard atau Settings, lalu bagikan via WhatsApp, email, Instagram, Facebook, atau media sosial lainnya. Kami menyediakan template pesan WhatsApp yang bisa Anda gunakan.',
                    ],
                    [
                        'question' => 'Apakah bisa buat undangan untuk setiap tamu?',
                        'answer' => 'Saat ini Akadnya.com menggunakan satu URL untuk semua tamu. Fitur personalized invitation (URL unik per tamu) sedang dalam pengembangan dan akan segera hadir.',
                    ],
                ],
            ],
            [
                'category' => 'Teknis & Troubleshooting',
                'icon' => 'tool',
                'questions' => [
                    [
                        'question' => 'Undangan tidak bisa dibuka, kenapa?',
                        'answer' => 'Pastikan: 1) Undangan sudah dipublikasikan, 2) URL yang dibagikan benar, 3) Koneksi internet stabil, 4) Browser sudah update. Jika masih bermasalah, coba clear cache browser atau gunakan browser lain.',
                    ],
                    [
                        'question' => 'Foto tidak muncul di undangan, kenapa?',
                        'answer' => 'Kemungkinan: 1) Foto masih diupload, tunggu beberapa saat, 2) Ukuran foto terlalu besar, kompres dulu, 3) Format foto tidak didukung, gunakan JPG/PNG, 4) Koneksi internet lambat. Coba refresh halaman.',
                    ],
                    [
                        'question' => 'Musik tidak autoplay, kenapa?',
                        'answer' => 'Browser modern (Chrome, Safari, Firefox) memblokir autoplay audio untuk pengalaman user yang lebih baik. Tamu perlu klik tombol musik atau tombol "Buka Undangan" untuk memutar musik.',
                    ],
                    [
                        'question' => 'Custom domain tidak bisa diakses, kenapa?',
                        'answer' => 'Tunggu propagasi DNS (bisa sampai 24 jam). Cek apakah CNAME record sudah benar di DNS Management. Gunakan tools seperti whatsmydns.net untuk cek status propagasi. Jika masih bermasalah, hubungi support.',
                    ],
                    [
                        'question' => 'Bagaimana cara menghubungi support?',
                        'answer' => sprintf(
                            'Anda bisa menghubungi kami via Email: %s atau WhatsApp: %s (Senin-Jumat, 09:00-17:00 WIB). Kami siap membantu Anda!',
                            SiteSetting::get('support_email', 'support@akadnya.com'),
                            $this->formatWhatsApp(SiteSetting::get('support_whatsapp', '6282374338273'))
                        ),
                    ],
                ],
            ],
        ];
    }

    /**
     * Format a WhatsApp number (e.g. 6282374338273) into +62 823-7433-8273.
     */
    private function formatWhatsApp(string $number): string
    {
        $digits = preg_replace('/\D/', '', $number);

        if (str_starts_with($digits, '62')) {
            $digits = substr($digits, 2);
        }

        return '+62 '.implode('-', str_split($digits, 4));
    }
}
