<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PublicNavbar from '@/components/PublicNavbar.vue';
import PublicFooter from '@/components/PublicFooter.vue';

interface Meta {
    title: string;
    description: string;
}

defineProps<{
    meta: Meta;
}>();

const updatedDate = 'Agustus 2026';
</script>

<template>
    <div>
        <Head :title="meta.title">
            <meta name="description" :content="meta.description" />
            <meta property="og:type" content="website" />
            <meta property="og:title" :content="meta.title" />
            <meta property="og:description" :content="meta.description" />
            <meta property="twitter:card" content="summary_large_image" />
            <meta property="twitter:title" :content="meta.title" />
            <meta property="twitter:description" :content="meta.description" />
        </Head>

        <div class="my-page">
            <PublicNavbar />

            <main class="my-container pt-28 pb-16">
                <div class="mx-auto max-w-3xl">
                    <p class="my-label mb-4">Legal</p>
                    <h1 class="my-heading mb-6 text-4xl md:text-5xl">
                        Syarat &amp; Ketentuan
                    </h1>
                    <p class="mb-12 text-[var(--my-muted)]">
                        Terakhir diperbarui: {{ updatedDate }}
                    </p>

                    <div class="my-card grid gap-8 p-7 md:p-10">
                        <p class="my-copy">
                            Dengan mengakses atau menggunakan Layanan MyAkad
                            (selanjutnya disebut "Layanan") di
                            myakad.refaadstack.com, Anda menyetujui syarat dan
                            ketentuan berikut. Jika Anda tidak setuju, mohon
                            berhenti menggunakan Layanan.
                        </p>

                        <section class="grid gap-3">
                            <h2 class="my-heading text-2xl">1. Definisi</h2>
                            <ol
                                class="grid list-decimal gap-2 pl-5 text-sm leading-6 text-[var(--my-muted)]"
                            >
                                <li>
                                    <strong>MyAkad</strong> adalah platform
                                    undangan digital yang memungkinkan Pengguna
                                    membuat undangan dengan template, mengelola
                                    tamu, RSVP, amplop digital, dan fitur
                                    pendukung lainnya.
                                </li>
                                <li>
                                    <strong>Pengguna</strong> adalah setiap
                                    orang yang mendaftar atau menggunakan
                                    Layanan.
                                </li>
                                <li>
                                    <strong>Template</strong> adalah desain
                                    undangan digital yang dapat dibeli.
                                </li>
                                <li>
                                    <strong>Produk tambahan (add-on)</strong>
                                    adalah layanan pendukung yang dijual
                                    terpisah, misalnya buku tamu digital dan
                                    custom domain.
                                </li>
                                <li>
                                    <strong>Konten</strong> adalah seluruh
                                    materi yang diunggah atau diisi Pengguna ke
                                    dalam Layanan.
                                </li>
                            </ol>
                        </section>

                        <section class="grid gap-3">
                            <h2 class="my-heading text-2xl">
                                2. Penerimaan Syarat
                            </h2>
                            <p class="my-copy">
                                Dengan mendaftar, membeli, atau memublikasikan
                                undangan, Anda dianggap telah membaca, memahami,
                                dan menyetujui seluruh ketentuan ini beserta
                                Kebijakan Privasi MyAkad.
                            </p>
                        </section>

                        <section class="grid gap-3">
                            <h2 class="my-heading text-2xl">
                                3. Akun dan Pendaftaran
                            </h2>
                            <ol
                                class="list-decimal gap-2 pl-5 text-sm leading-6 text-[var(--my-muted)]"
                            >
                                <li>
                                    Anda bertanggung jawab atas keamanan akun
                                    dan seluruh aktivitas pada akun Anda.
                                </li>
                                <li>
                                    Data akun harus akurat dan mutakhir.
                                    Kehilangan akses akibat informasi yang salah
                                    bukan tanggung jawab MyAkad.
                                </li>
                                <li>
                                    Akun tidak boleh dibagikan, dijual, atau
                                    dipindahtangankan tanpa izin.
                                </li>
                                <li>
                                    Dengan mendaftar, Anda menyatakan telah
                                    membaca dan menyetujui seluruh isi halaman
                                    ini serta Kebijakan Privasi MyAkad.
                                </li>
                            </ol>
                        </section>

                        <section class="grid gap-3">
                            <h2 class="my-heading text-2xl">
                                4. Produk dan Pembelian
                            </h2>
                            <ol
                                class="list-decimal gap-2 pl-5 text-sm leading-6 text-[var(--my-muted)]"
                            >
                                <li>
                                    Template dan produk tambahan dibeli dengan
                                    harga yang tertera saat checkout. Harga yang
                                    tampil saat pembayaran adalah yang berlaku.
                                </li>
                                <li>
                                    <strong>Multi-template</strong> memungkinkan
                                    Anda memiliki lebih dari satu undangan
                                    aktif; setiap undangan dikelola terpisah di
                                    dashboard.
                                </li>
                                <li>
                                    Harga dapat berubah sewaktu-waktu tanpa
                                    pemberitahuan.
                                </li>
                                <li>
                                    Pembayaran diproses melalui payment gateway
                                    yang aman; MyAkad tidak menyimpan data kartu
                                    kredit/debit secara utuh.
                                </li>
                            </ol>
                        </section>

                        <section class="grid gap-3">
                            <h2 class="my-heading text-2xl">
                                5. Hak Kekayaan Intelektual
                            </h2>
                            <ol
                                class="list-decimal gap-2 pl-5 text-sm leading-6 text-[var(--my-muted)]"
                            >
                                <li>
                                    Seluruh template, desain, kode, dan tampilan
                                    Layanan milik MyAkad yang tidak boleh
                                    disalin, dijual, atau didistribusikan ulang.
                                </li>
                                <li>
                                    Konten yang Anda unggah tetap milik Anda;
                                    Anda memberi MyAkad lisensi terbatas untuk
                                    menyimpan dan menampilkan Konten agar
                                    Layanan berfungsi.
                                </li>
                                <li>
                                    Konten yang Anda unggah tidak boleh
                                    melanggar hak cipta pihak lain.
                                </li>
                            </ol>
                        </section>

                        <section class="grid gap-3">
                            <h2 class="my-heading text-2xl">
                                6. Konten dan Tanggung Jawab Pengguna
                            </h2>
                            <ol
                                class="list-decimal gap-2 pl-5 text-sm leading-6 text-[var(--my-muted)]"
                            >
                                <li>
                                    Anda bertanggung jawab penuh atas kebenaran,
                                    kelengkapan, dan legalitas Konten undangan
                                    Anda.
                                </li>
                                <li>
                                    Dilarang mengunggah Konten yang melanggar
                                    hukum, mengandung konten tidak pantas, atau
                                    melanggar hak pihak ketiga.
                                </li>
                                <li>
                                    MyAkad berhak menahan atau menghapus
                                    undangan yang melanggar ketentuan ini tanpa
                                    pemberitahuan.
                                </li>
                            </ol>
                        </section>

                        <section class="grid gap-3">
                            <h2 class="my-heading text-2xl">
                                7. Subdomain dan Custom Domain
                            </h2>
                            <ol
                                class="list-decimal gap-2 pl-5 text-sm leading-6 text-[var(--my-muted)]"
                            >
                                <li>
                                    Subdomain diberikan unik; subdomain yang
                                    sudah dipakai tidak dapat digunakan Pengguna
                                    lain.
                                </li>
                                <li>
                                    Anda dapat mengubah subdomain kapan saja,
                                    namun link lama tidak akan berfungsi lagi.
                                </li>
                                <li>
                                    Custom domain hanya tersedia bagi Pengguna
                                    yang memenuhi syarat. Pengaturan DNS domain
                                    adalah tanggung jawab Anda.
                                </li>
                            </ol>
                        </section>

                        <section class="grid gap-3">
                            <h2 class="my-heading text-2xl">
                                8. Pembatalan dan Pengembalian Dana (Refund)
                            </h2>
                            <ol
                                class="list-decimal gap-2 pl-5 text-sm leading-6 text-[var(--my-muted)]"
                            >
                                <li>
                                    Karena template dan produk tambahan
                                    merupakan produk digital, pembelian dianggap
                                    final setelah checkout, termasuk jika
                                    terjadi salah pilih template maupun produk.
                                </li>
                                <li>
                                    Pengembalian dana hanya dapat dilakukan
                                    dalam kondisi yang wajar dan disetujui oleh
                                    MyAkad, misalnya karena kesalahan dari pihak
                                    MyAkad (produk tidak dapat digunakan) atau
                                    kondisi khusus lainnya yang memang layak.
                                </li>
                                <li>
                                    Refund diproses melalui payment gateway dan
                                    dapat memerlukan waktu beberapa hari kerja
                                    serta akan diverifikasi terlebih dahulu oleh
                                    tim kami.
                                </li>
                            </ol>
                        </section>

                        <section class="grid gap-3">
                            <h2 class="my-heading text-2xl">
                                9. Ketersediaan dan Penghentian Layanan
                            </h2>
                            <p class="my-copy">
                                Layanan dapat mengalami pemeliharaan
                                (maintenance) atau downtime sementara. MyAkad
                                berhak menonaktifkan atau menahan akses akun
                                yang melanggar ketentuan. Layanan infrastruktur
                                pihak ketiga berada di luar kendali MyAkad.
                            </p>
                        </section>

                        <section class="grid gap-3">
                            <h2 class="my-heading text-2xl">
                                10. Jaminan dan Batasan Tanggung Jawab
                            </h2>
                            <ol
                                class="list-decimal gap-2 pl-5 text-sm leading-6 text-[var(--my-muted)]"
                            >
                                <li>
                                    Layanan diberikan "sebagaimana ada" (as-is).
                                    MyAkad tidak menjamin Layanan bebas gangguan
                                    atau bebas dari kesalahan.
                                </li>
                                <li>
                                    MyAkad tidak bertanggung jawab atas
                                    kehilangan data, kegagalan akses, perubahan
                                    link, atau kerugian tidak langsung.
                                </li>
                                <li>
                                    Sejauh diizinkan hukum, kewajiban MyAkad
                                    dibatasi pada jumlah yang Anda bayarkan
                                    untuk Layanan.
                                </li>
                            </ol>
                        </section>

                        <section class="grid gap-3">
                            <h2 class="my-heading text-2xl">
                                11. Perubahan Syarat &amp; Ketentuan
                            </h2>
                            <p class="my-copy">
                                MyAkad dapat mengubah syarat &amp; ketentuan
                                sewaktu-waktu. Perubahan berlaku sejak diumumkan
                                di halaman ini. Kelanjutan penggunaan Layanan
                                berarti Anda menerima perubahan tersebut.
                            </p>
                        </section>

                        <section class="grid gap-3">
                            <h2 class="my-heading text-2xl">
                                12. Hukum yang Berlaku
                            </h2>
                            <p class="my-copy">
                                Syarat &amp; ketentuan ini diatur oleh hukum
                                yang berlaku di Indonesia. Perselisihan
                                diupayakan diselesaikan secara musyawarah
                                terlebih dahulu.
                            </p>
                        </section>

                        <section class="grid gap-3">
                            <h2 class="my-heading text-2xl">13. Kontak</h2>
                            <p class="my-copy">
                                Pertanyaan terkait syarat &amp; ketentuan dapat
                                disampaikan ke
                                <a
                                    class="font-semibold text-[var(--my-primary)]"
                                    href="mailto:support@myakad.com"
                                    >support@myakad.com</a
                                >.
                            </p>
                        </section>
                    </div>
                </div>
            </main>

            <PublicFooter />
        </div>
    </div>
</template>
