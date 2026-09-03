<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import PublicNavbar from '@/components/PublicNavbar.vue';
import PublicFooter from '@/components/PublicFooter.vue';

const supportEmail = usePage().props.support.email;

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
                        Kebijakan Privasi
                    </h1>
                    <p class="mb-12 text-[var(--my-muted)]">
                        Terakhir diperbarui: {{ updatedDate }}
                    </p>

                    <div class="my-card grid gap-8 p-7 md:p-10">
                        <p class="my-copy">
                            Akadnya.com menghormati kerahasiaan informasi
                            pribadi Anda. Kebijakan ini menjelaskan data yang
                            kami kumpulkan, cara penggunaannya, penyimpanannya,
                            dan pilihan yang Anda miliki atas data Anda.
                        </p>

                        <section class="grid gap-3">
                            <h2 class="my-heading text-2xl">
                                1. Data yang Kami Kumpulkan
                            </h2>
                            <h3
                                class="text-base font-bold text-[var(--my-neutral)]"
                            >
                                a. Data Akun
                            </h3>
                            <ul
                                class="list-disc gap-2 pl-5 text-sm leading-6 text-[var(--my-muted)]"
                            >
                                <li>
                                    Nama, alamat email, dan kata sandi yang Anda
                                    daftarkan.
                                </li>
                                <li>
                                    Tanggal pendaftaran dan aktivitas login di
                                    dalam Layanan.
                                </li>
                            </ul>

                            <h3
                                class="text-base font-bold text-[var(--my-neutral)]"
                            >
                                b. Data Konten Undangan
                            </h3>
                            <ul
                                class="list-disc gap-2 pl-5 text-sm leading-6 text-[var(--my-muted)]"
                            >
                                <li>
                                    Detail undangan: nama mempelai, tanggal
                                    &amp; tempat acara, galeri, cerita, amplop
                                    digital, dan lainnya.
                                </li>
                                <li>Foto dan musik yang Anda unggah.</li>
                                <li>
                                    Data tamu yang Anda kelola (nama, nomor
                                    WhatsApp, kategori, RSVP) dan ucapan dari
                                    tamu.
                                </li>
                            </ul>

                            <h3
                                class="text-base font-bold text-[var(--my-neutral)]"
                            >
                                c. Data Transaksi
                            </h3>
                            <ul
                                class="list-disc gap-2 pl-5 text-sm leading-6 text-[var(--my-muted)]"
                            >
                                <li>
                                    Riwayat pembelian template dan produk
                                    tambahan (produk, nominal, status
                                    pembayaran).
                                </li>
                                <li>
                                    Fitur yang aktif pada akun Anda, misalnya
                                    buku tamu digital.
                                </li>
                            </ul>

                            <h3
                                class="text-base font-bold text-[var(--my-neutral)]"
                            >
                                d. Data Teknis
                            </h3>
                            <ul
                                class="list-disc gap-2 pl-5 text-sm leading-6 text-[var(--my-muted)]"
                            >
                                <li>
                                    Alamat IP (IP address), jenis perangkat dan
                                    browser, halaman yang dikunjungi, serta log
                                    aktivitas, untuk menjaga keamanan dan
                                    kualitas Layanan.
                                </li>
                            </ul>
                        </section>

                        <section class="grid gap-3">
                            <h2 class="my-heading text-2xl">
                                2. Cara Penggunaan Data
                            </h2>
                            <p class="my-copy">Data kami gunakan untuk:</p>
                            <ol
                                class="list-decimal gap-2 pl-5 text-sm leading-6 text-[var(--my-muted)]"
                            >
                                <li>
                                    Mengoperasikan dan mengelola akun, template,
                                    undangan, dan seluruh fitur Layanan.
                                </li>
                                <li>
                                    Memproses pembayaran melalui payment
                                    gateway.
                                </li>
                                <li>
                                    Menjaga keamanan dan mencegah penyalahgunaan
                                    atau penipuan.
                                </li>
                                <li>
                                    Mendukung dan menanggapi pertanyaan dari
                                    Anda.
                                </li>
                                <li>
                                    Menyusun statistik agregat (jumlah
                                    pengunjung, RSVP, dan lainnya) tanpa
                                    mengidentifikasi individu.
                                </li>
                            </ol>
                            <p class="my-copy">
                                Kami tidak menjual data pribadi Anda kepada
                                pihak ketiga.
                            </p>
                        </section>

                        <section class="grid gap-3">
                            <h2 class="my-heading text-2xl">
                                3. Data Tamu dan Tanggung Jawab Anda
                            </h2>
                            <p class="my-copy">
                                Jika Anda memasukkan data tamu ke dalam Layanan,
                                Anda wajib memastikan data tersebut diperoleh
                                secara sah. Akadnya.com memproses data tamu
                                hanya untuk menjalankan Layanan atas nama Anda.
                            </p>
                        </section>

                        <section class="grid gap-3">
                            <h2 class="my-heading text-2xl">
                                4. Penyimpanan Data
                            </h2>
                            <p class="my-copy">
                                Data disimpan secara elektronis pada
                                infrastruktur kami atau penyedia layanan yang
                                kami gunakan, dengan langkah keamanan teknis dan
                                organisasi untuk mencegah akses tidak sah,
                                perubahan, atau kehilangan data.
                            </p>
                        </section>

                        <section class="grid gap-3">
                            <h2 class="my-heading text-2xl">
                                5. Pembagian Data dengan Pihak Ketiga
                            </h2>
                            <p class="my-copy">
                                Data dapat dibagikan secara terbatas kepada
                                pihak yang benar-benar berperan dalam
                                menjalankan Layanan:
                            </p>
                            <ul
                                class="list-disc gap-2 pl-5 text-sm leading-6 text-[var(--my-muted)]"
                            >
                                <li>
                                    <strong>Payment gateway</strong>: untuk
                                    memproses pembayaran Anda.
                                </li>
                                <li>
                                    <strong>Hosting/penyimpanan</strong>:
                                    penyedia server tempat data Layanan
                                    tersimpan.
                                </li>
                                <li>
                                    <strong>Penyedia domain/DNS</strong>: untuk
                                    mengaktifkan subdomain atau custom domain
                                    Anda.
                                </li>
                                <li>
                                    <strong>Penyedia komunikasi</strong>:
                                    email/WhatsApp bila Anda memakai fitur
                                    pengiriman.
                                </li>
                            </ul>
                            <p class="my-copy">
                                Pihak ketiga tersebut tidak diizinkan
                                menggunakan data Anda di luar kepentingan yang
                                telah disebut.
                            </p>
                        </section>

                        <section class="grid gap-3">
                            <h2 class="my-heading text-2xl">6. Cookie</h2>
                            <p class="my-copy">
                                Layanan menggunakan cookie untuk menjaga sesi
                                login dan kenyamanan penggunaan (misalnya
                                keranjang belanja). Anda dapat menonaktifkan
                                cookie melalui pengaturan browser, namun
                                sebagian fitur (misalnya login) mungkin tidak
                                berfungsi penuh.
                            </p>
                        </section>

                        <section class="grid gap-3">
                            <h2 class="my-heading text-2xl">
                                7. Konten Publik pada Undangan
                            </h2>
                            <p class="my-copy">
                                Saat undangan dipublikasikan, isi undangan yang
                                Anda tampilkan (nama, tanggal, tempat, galeri,
                                dan lainnya) dapat diakses publik melalui link
                                undangan. Pastikan informasi tersebut memang
                                ingin Anda bagikan kepada tamu. Data tamu dan
                                RSVP bersifat pribadi dan hanya dapat diakses
                                oleh Anda melalui dashboard.
                            </p>
                        </section>

                        <section class="grid gap-3">
                            <h2 class="my-heading text-2xl">
                                8. Hak Anda atas Data
                            </h2>
                            <ol
                                class="list-decimal gap-2 pl-5 text-sm leading-6 text-[var(--my-muted)]"
                            >
                                <li>
                                    <strong>Akses</strong> — melihat data yang
                                    kami simpan.
                                </li>
                                <li>
                                    <strong>Perbaikan</strong> — memperbaiki
                                    data yang salah atau tidak lengkap.
                                </li>
                                <li>
                                    <strong>Penghapusan</strong> — meminta
                                    penghapusan akun beserta datanya.
                                </li>
                                <li>
                                    <strong>Ekspor</strong> — memperoleh data
                                    dalam format yang dapat dibaca.
                                </li>
                            </ol>
                            <p class="my-copy">
                                Untuk menggunakan hak-hak tersebut, hubungi
                                <a
                                    class="font-semibold text-[var(--my-primary)]"
                                    :href="`mailto:${supportEmail}`"
                                    >{{ supportEmail }}</a
                                >.
                            </p>
                        </section>

                        <section class="grid gap-3">
                            <h2 class="my-heading text-2xl">
                                9. Perlindungan Anak
                            </h2>
                            <p class="my-copy">
                                Layanan ditujukan bagi pengguna yang telah
                                memenuhi syarat umur sesuai hukum yang berlaku.
                                Kami tidak sengaja mengumpulkan data anak di
                                bawah umur tanpa persetujuan orang tua atau
                                wali.
                            </p>
                        </section>

                        <section class="grid gap-3">
                            <h2 class="my-heading text-2xl">
                                10. Keamanan Pembayaran
                            </h2>
                            <p class="my-copy">
                                Detail transaksi diproses dan disimpan sesuai
                                kebijakan penyedia payment gateway. Akadnya.com
                                tidak menyimpan data kartu kredit atau debit
                                Anda secara utuh.
                            </p>
                        </section>

                        <section class="grid gap-3">
                            <h2 class="my-heading text-2xl">
                                11. Perubahan Kebijakan
                            </h2>
                            <p class="my-copy">
                                Kebijakan ini dapat diperbarui sewaktu-waktu dan
                                perubahan akan diumumkan di halaman ini. Untuk
                                perubahan yang signifikan, kami akan berusaha
                                menginformasikannya melalui email.
                            </p>
                        </section>

                        <section class="grid gap-3">
                            <h2 class="my-heading text-2xl">12. Kontak</h2>
                            <p class="my-copy">
                                Pertanyaan terkait kebijakan privasi dapat
                                disampaikan ke
                                <a
                                    class="font-semibold text-[var(--my-primary)]"
                                    :href="`mailto:${supportEmail}`"
                                    >{{ supportEmail }}</a
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
