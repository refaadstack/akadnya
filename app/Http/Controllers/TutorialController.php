<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class TutorialController extends Controller
{
    /**
     * Show tutorial page covering all menus.
     */
    public function index(): Response
    {
        return Inertia::render('Tutorial', [
            'groups' => $this->getTutorialData(),
            'meta' => [
                'title' => 'Tutorial - Panduan Menggunakan Seluruh Menu | Akadnya.com',
                'description' => 'Panduan lengkap menggunakan semua menu Akadnya.com: mulai dari koleksi template, produk, keranjang, hingga editor undangan, galeri, tamu, RSVP, buku tamu, dan publikasi undangan.',
                'keywords' => 'tutorial akadnya, panduan undangan digital, cara membuat undangan online, cara pakai akadnya, tutorial editor undangan, cara publish undangan, cara cek rsvp, cara isi galeri undangan',
            ],
            'canRegister' => Route::has('register'),
        ]);
    }

    /**
     * Get tutorial data organized by menu groups.
     */
    private function getTutorialData(): array
    {
        return [
            [
                'group' => 'Menu Landing Page',
                'icon' => 'home',
                'menus' => [
                    [
                        'name' => 'Koleksi',
                        'route' => '/templates',
                        'description' => 'Menjelajahi dan memilih template undangan yang ingin dipakai.',
                        'steps' => [
                            ['title' => 'Buka menu Koleksi', 'detail' => 'Klik menu "Koleksi" di bagian atas halaman utama (atau tombol "Pilih Template Undanganku" di hero).'],
                            ['title' => 'Preview template', 'detail' => 'Hover atau klik template untuk melihat pratinjau. Klik "Lihat Detail" untuk membuka halaman detail template lengkap dengan fitur yang tersedia.'],
                            ['title' => 'Coba preview interaktif', 'detail' => 'Di halaman detail template, gunakan tombol preview untuk melihat tampilan undangan secara langsung dengan contoh konten.'],
                            ['title' => 'Tambahkan ke keranjang', 'detail' => 'Jika suka, klik tombol "Tambah ke Keranjang" lalu lanjutkan ke checkout. Template yang sudah dipilih tidak bisa diganti setelah pembelian.'],
                        ],
                    ],
                    [
                        'name' => 'Produk',
                        'route' => '/produk',
                        'description' => 'Melihat produk tambahan (add-on) selain template undangan.',
                        'steps' => [
                            ['title' => 'Buka menu Produk', 'detail' => 'Klik menu "Produk" di navbar untuk melihat daftar produk tambahan seperti Buku Tamu Digital.'],
                            ['title' => 'Pilih produk yang dibutuhkan', 'detail' => 'Baca deskripsi dan harga setiap produk. Klik "Lihat Detail" untuk informasi lengkap fitur produk.'],
                            ['title' => 'Tambahkan ke keranjang', 'detail' => 'Klik tombol tambah ke keranjang pada produk yang diinginkan. Produk add-on bisa digabung dengan template dalam satu transaksi.'],
                        ],
                    ],
                    [
                        'name' => 'Cara Pesan',
                        'route' => '/#how-it-works',
                        'description' => 'Ringkasan alur pemesanan undangan digital Akadnya.com.',
                        'steps' => [
                            ['title' => 'Buka bagian Cara Pesan', 'detail' => 'Klik menu "Cara Pesan" di navbar, halaman akan scroll ke bagian penjelasan alur pemesanan di halaman utama.'],
                            ['title' => 'Ikuti 4 langkah', 'detail' => 'Alurnya: pilih template, buat akun & bayar, isi konten undangan, lalu publikasikan dan bagikan ke tamu.'],
                        ],
                    ],
                    [
                        'name' => 'FAQ',
                        'route' => '/faq',
                        'description' => 'Pusat bantuan untuk pertanyaan yang sering diajukan.',
                        'steps' => [
                            ['title' => 'Buka menu FAQ', 'detail' => 'Klik menu "FAQ" di navbar atau link FAQ di footer.'],
                            ['title' => 'Cari pertanyaan', 'detail' => 'Gunakan kotak pencarian atau klik kategori (Umum, Paket & Harga, Template & Desain, dll).'],
                            ['title' => 'Baca jawaban', 'detail' => 'Klik pertanyaan untuk membuka jawabannya. Jika tidak menemukan jawaban, hubungi support via email atau WhatsApp.'],
                        ],
                    ],
                    [
                        'name' => 'Masuk / Mulai Desain',
                        'route' => '/register',
                        'description' => 'Membuat akun atau masuk ke akun Akadnya.com.',
                        'steps' => [
                            ['title' => 'Klik "Mulai Desain"', 'detail' => 'Tombol "Mulai Desain" di navbar membawa Anda ke halaman pendaftaran. Isi nama, email, dan password.'],
                            ['title' => 'Verifikasi email', 'detail' => 'Cek inbox email Anda dan klik link verifikasi yang dikirim Akadnya.com untuk mengaktifkan akun.'],
                            ['title' => 'Masuk ke akun', 'detail' => 'Jika sudah punya akun, klik "Masuk" di navbar dan gunakan email serta password Anda.'],
                        ],
                    ],
                ],
            ],
            [
                'group' => 'Menu Dashboard',
                'icon' => 'settings',
                'menus' => [
                    [
                        'name' => 'Dashboard',
                        'route' => '/dashboard',
                        'description' => 'Halaman utama setelah login untuk mengelola undangan Anda.',
                        'steps' => [
                            ['title' => 'Buka Dashboard', 'detail' => 'Klik "Dashboard" di navbar (atau di menu pengguna) setelah login. Di sini Anda melihat ringkasan undangan, status, dan tombol aksi cepat.'],
                            ['title' => 'Ganti undangan aktif', 'detail' => 'Jika punya lebih dari satu undangan, gunakan dropdown "Pilih Undangan Aktif" di navbar untuk berpindah antara undangan.'],
                            ['title' => 'Mulai dari sini', 'detail' => 'Ikuti langkah yang disarankan: isi Editor, lengkapi Kustomisasi, tambah foto di Galeri, lalu Publikasikan di Pengaturan.'],
                        ],
                    ],
                    [
                        'name' => 'Editor',
                        'route' => '/dashboard/editor',
                        'description' => 'Mengisi konten undangan: nama mempelai, tanggal, lokasi, dan cerita.',
                        'steps' => [
                            ['title' => 'Buka menu Editor', 'detail' => 'Klik "Editor" di menu dashboard untuk mulai mengisi konten undangan.'],
                            ['title' => 'Isi data wajib', 'detail' => 'Lengkapi nama mempelai (pria & wanita), orang tua, tanggal & waktu akad/resepsi, lokasi, dan alamat. Data wajib perlu diisi sebelum bisa publikasi.'],
                            ['title' => 'Lengkapi konten lainnya', 'detail' => 'Isi judul acara, doa, kutipan, cerita cinta (love story), galeri, musik latar, RSVP, dan amplop digital sesuai kebutuhan.'],
                            ['title' => 'Simpan perubahan', 'detail' => 'Klik "Simpan" di bagian bawah editor. Pastikan muncul notifikasi berhasil sebelum berpindah menu.'],
                        ],
                    ],
                    [
                        'name' => 'Kustomisasi',
                        'route' => '/dashboard/customize',
                        'description' => 'Mengatur urutan section dan ornamen dekorasi undangan.',
                        'steps' => [
                            ['title' => 'Buka menu Kustomisasi', 'detail' => 'Klik "Kustomisasi" di menu dashboard.'],
                            ['title' => 'Atur urutan section', 'detail' => 'Gunakan tombol naik/turun untuk mengubah urutan bagian undangan (cover, countdown, akad, galeri, dll).'],
                            ['title' => 'Tampilkan/sembunyikan section', 'detail' => 'Toggle setiap section untuk menampilkan atau menyembunyikannya dari undangan publik.'],
                            ['title' => 'Atur ornamen', 'detail' => 'Nyalakan atau matikan ornamen dekorasi (misal bunga, pita) agar sesuai selera Anda.'],
                        ],
                    ],
                    [
                        'name' => 'Galeri',
                        'route' => '/dashboard/gallery',
                        'description' => 'Mengelola foto pre-wedding dan foto-foto lain di undangan.',
                        'steps' => [
                            ['title' => 'Buka menu Galeri', 'detail' => 'Klik "Galeri" di menu dashboard untuk mengelola foto undangan.'],
                            ['title' => 'Upload foto', 'detail' => 'Klik tombol upload dan pilih foto (JPG/PNG/WebP, maksimal 5MB per foto). Disarankan 6-12 foto untuk performa optimal.'],
                            ['title' => 'Atur urutan & hapus', 'detail' => 'Gunakan tombol panah untuk mengatur urutan foto, dan tombol hapus untuk menghapus foto yang tidak dipakai.'],
                        ],
                    ],
                    [
                        'name' => 'Tamu',
                        'route' => '/dashboard/guests',
                        'description' => 'Mengelola daftar tamu undangan dan mengirim undangan.',
                        'steps' => [
                            ['title' => 'Buka menu Tamu', 'detail' => 'Klik "Tamu" di menu dashboard untuk membuka daftar tamu.'],
                            ['title' => 'Tambah tamu', 'detail' => 'Klik "Tambah Tamu", isi nama dan nomor WhatsApp. Bisa juga import dari file Excel/CSV.'],
                            ['title' => 'Kirim undangan via WhatsApp', 'detail' => 'Klik ikon WhatsApp pada baris tamu untuk mengirim undangan dengan template pesan yang sudah disediakan.'],
                            ['title' => 'Export daftar tamu', 'detail' => 'Gunakan tombol export untuk mengunduh daftar tamu (dengan status RSVP) ke file Excel/CSV.'],
                        ],
                    ],
                    [
                        'name' => 'RSVP',
                        'route' => '/dashboard/rsvp',
                        'description' => 'Melihat konfirmasi kehadiran dari tamu.',
                        'steps' => [
                            ['title' => 'Buka menu RSVP', 'detail' => 'Klik "RSVP" di menu dashboard.'],
                            ['title' => 'Lihat statistik', 'detail' => 'Ringkasan jumlah tamu yang hadir, tidak hadir, dan belum konfirmasi ditampilkan di bagian atas.'],
                            ['title' => 'Cek detail konfirmasi', 'detail' => 'Lihat daftar tamu yang sudah mengisi RSVP beserta jumlah pax dan pesan yang mereka tinggalkan.'],
                        ],
                    ],
                    [
                        'name' => 'Buku Tamu',
                        'route' => '/dashboard/guest-book',
                        'description' => 'Check-in tamu dengan QR code saat acara berlangsung.',
                        'steps' => [
                            ['title' => 'Buka menu Buku Tamu', 'detail' => 'Klik "Buku Tamu" di menu dashboard untuk melihat fitur check-in digital.'],
                            ['title' => 'Cetak QR per tamu', 'detail' => 'Unduh/print QR code unik setiap tamu untuk ditempel di meja atau dikirim sebelum acara.'],
                            ['title' => 'Scan saat tamu datang', 'detail' => 'Gunakan menu "Scan" untuk memindai QR tamu saat tiba, kehadiran tercatat otomatis.'],
                            ['title' => 'Souvenir & undian', 'detail' => 'Catat souvenir yang sudah diambil per tamu, dan gunakan fitur undian untuk memilih pemenang dari tamu yang hadir.'],
                        ],
                    ],
                    [
                        'name' => 'Love Story',
                        'route' => '/dashboard/love-story',
                        'description' => 'Menulis cerita cinta atau timeline perjalanan Anda berdua.',
                        'steps' => [
                            ['title' => 'Buka menu Love Story', 'detail' => 'Klik "Love Story" di menu dashboard.'],
                            ['title' => 'Tulis cerita', 'detail' => 'Tambah momen-momen penting: pertama bertemu, lamaran, dan lainnya beserta tanggal dan cerita singkat.'],
                            ['title' => 'Simpan', 'detail' => 'Klik simpan dan cerita akan tampil di bagian love story undangan publik.'],
                        ],
                    ],
                    [
                        'name' => 'Pengaturan',
                        'route' => '/dashboard/settings',
                        'description' => 'Mengelola subdomain, custom domain, dan publikasi undangan.',
                        'steps' => [
                            ['title' => 'Buka menu Pengaturan', 'detail' => 'Klik "Pengaturan" di menu dashboard.'],
                            ['title' => 'Atur subdomain', 'detail' => 'Tulis subdomain yang diinginkan (minimal 3 karakter, huruf kecil, angka, tanda hubung) atau klik "Generate Random", lalu simpan.'],
                            ['title' => 'Pasang custom domain', 'detail' => 'Untuk paket Premium/Lifetime: buat CNAME record di DNS provider mengarah ke akadnya.com, tunggu propagasi, lalu isi domain di sini.'],
                            ['title' => 'Publikasikan undangan', 'detail' => 'Setelah semua data lengkap, klik tombol "Publikasikan". Undangan langsung bisa diakses di URL subdomain Anda. Gunakan tombol yang sama untuk unpublish jika perlu.'],
                        ],
                    ],
                    [
                        'name' => 'Transaksi',
                        'route' => '/dashboard/transactions',
                        'description' => 'Riwayat pembelian dan status pembayaran.',
                        'steps' => [
                            ['title' => 'Buka menu Transaksi', 'detail' => 'Klik "Transaksi" dari dashboard untuk melihat riwayat pembelian Anda.'],
                            ['title' => 'Cek status pembayaran', 'detail' => 'Pantau status tiap transaksi (pending, sukses, gagal) dan detail pesanan yang dibeli.'],
                        ],
                    ],
                ],
            ],
            [
                'group' => 'Menu Transaksi & Lainnya',
                'icon' => 'cart',
                'menus' => [
                    [
                        'name' => 'Keranjang & Checkout',
                        'route' => '/keranjang',
                        'description' => 'Memproses pembelian template dan produk tambahan.',
                        'steps' => [
                            ['title' => 'Buka Keranjang', 'detail' => 'Klik ikon keranjang di navbar untuk melihat isi keranjang Anda.'],
                            ['title' => 'Atur jumlah & hapus item', 'detail' => 'Ubah jumlah atau hapus item yang tidak diinginkan, lalu klik "Checkout".'],
                            ['title' => 'Lakukan pembayaran', 'detail' => 'Pilih metode pembayaran (Transfer Bank, E-wallet, QRIS) lalu selesaikan pembayaran melalui Midtrans.'],
                            ['title' => 'Cek status di Transaksi', 'detail' => 'Setelah bayar, cek status di menu Transaksi. Template langsung aktif di dashboard setelah pembayaran berhasil.'],
                        ],
                    ],
                    [
                        'name' => 'Undangan Publik',
                        'route' => '/i/{subdomain}',
                        'description' => 'Tampilan undangan yang dilihat oleh tamu Anda.',
                        'steps' => [
                            ['title' => 'Bagikan link undangan', 'detail' => 'Copy URL subdomain (contoh: /i/nama-anda) dari Dashboard atau Pengaturan, lalu bagikan ke tamu via WhatsApp atau media sosial.'],
                            ['title' => 'Tamu buka tanpa login', 'detail' => 'Tamu bisa langsung membuka undangan, melihat konten, memutar musik, mengisi RSVP, dan mengirim ucapan tanpa perlu akun.'],
                            ['title' => 'Ucapan & doa', 'detail' => 'Tamu bisa meninggalkan ucapan di bagian wishes/ucapan yang tampil di undangan.'],
                        ],
                    ],
                    [
                        'name' => 'Footer & Legal',
                        'route' => '/terms',
                        'description' => 'Link bantuan dan dokumen legal di bagian bawah halaman.',
                        'steps' => [
                            ['title' => 'Menu footer', 'detail' => 'Footer berisi link cepat: Produk (Koleksi Template, Cara Pesan, Harga), Bantuan (FAQ, Support, WhatsApp), dan Legal.'],
                            ['title' => 'Kebijakan Privasi & Syarat', 'detail' => 'Baca dokumen Kebijakan Privasi (/privacy) dan Syarat & Ketentuan (/terms) untuk informasi penggunaan layanan.'],
                        ],
                    ],
                ],
            ],
        ];
    }
}