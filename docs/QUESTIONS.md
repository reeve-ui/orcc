# Pertanyaan Terbuka

Skeleton sudah berjalan, tetapi beberapa keputusan produk sebaiknya dikonfirmasi sebelum data asli dipakai penuh. Pertanyaan diberi prioritas: **P0** wajib sebelum produksi, **P1** penting untuk kenyamanan, **P2** pengembangan lanjutan.

## Sudah diputuskan

| Topik | Keputusan |
| --- | --- |
| Sumber data | Workbook ORCCDATA adalah format migrasi awal; berkas contoh tidak disertakan dalam repositori. Setelah impor, aplikasi menjadi sumber data utama. |
| Visibilitas jemaat | Jemaat melihat ringkasan anggota keluarganya; detail pribadi (NIK, kontak, email) hanya untuk dirinya sendiri. |
| Cakupan leader | Seluruh gereja, bukan per komsel. Posisi leader berada di atas pengerja: pendeta, ketua majelis, ketua yayasan, kepala sekretariat. |
| `SUKACITA` | Dipertahankan di `families.joy_number` untuk pengembangan relasi dengan aplikasi lain. |
| `KOMSEL` | Dipertahankan di `members.cell_group`, termasuk kode, untuk pengembangan relasi dengan aplikasi lain. |
| Kartu KKJ | A4 landscape. QR berisi tautan halaman verifikasi publik, bukan sekadar nomor KKJ. |
| Identitas gereja | Disimpan di tabel `church_settings` dan diubah admin lewat menu **Pengaturan Gereja**, termasuk logo. |
| Nomor KKJ | Otomatis: 5 karakter kode gereja + `00` + 2 digit tahun + 4 digit urutan; reset setiap tahun. Nomor lama tidak diubah. |
| Alamat | Berjenjang provinsi → kabupaten/kota → kecamatan → kelurahan → kode pos dari `database/data/kodepos.csv`; wajib diisi. |
| Foto keluarga | Jika tidak diunggah, otomatis memakai `public/images/family-default.png`. |

## P0 — wajib sebelum produksi

### 1. Sejauh mana jemaat boleh melihat data keluarganya? — **diputuskan**

Jemaat:

- dapat membuka halaman KKJ keluarganya,
- melihat nama, status, gender, usia, dan status pernikahan semua anggota keluarganya,
- hanya dapat membuka profil lengkap dirinya sendiri (NIK, kontak, email).

Keputusan: **pertahankan perilaku ini**. Tidak perlu perubahan policy.

### 2. Apakah leader melihat seluruh gereja atau hanya wilayah/komselnya? — **diputuskan**

Leader melihat **seluruh gereja**, bukan per komsel. Posisi leader berada di atas pengerja dan mencakup jabatan seperti pendeta, ketua majelis, ketua yayasan, dan kepala sekretariat. Tidak perlu tabel komsel atau filter global per wilayah untuk saat ini.

### 3. Sumber kebenaran data: Excel atau aplikasi? — **diputuskan**

Workbook ORCCDATA adalah **format untuk migrasi awal**. Berkas contoh tidak disertakan dalam repositori. Setelah impor, **aplikasi menjadi sumber data utama**. Impor ulang hanya untuk pemulihan atau migrasi, bukan sinkronisasi rutin.

### 4. Siapa yang berwenang mengoreksi data?

Perlu alur yang jelas untuk 112 WhatsApp, 10 NIK, dan 2 email yang tidak valid:

- siapa yang menghubungi jemaat,
- siapa yang boleh mengubah,
- apakah perubahan perlu persetujuan,
- apakah jemaat boleh memperbarui datanya sendiri.

Saat ini hanya staf yang dapat mengubah data.

### 5. Kebijakan privasi dan retensi

- Apakah jemaat sudah memberi persetujuan penyimpanan NIK dan tanggal lahir?
- Berapa lama data jemaat yang sudah pindah/meninggal disimpan?
- Apakah perlu fitur “nonaktifkan/arsipkan” alih-alih hapus permanen?

**Rekomendasi:** tambahkan status jemaat (aktif, pindah, meninggal, nonaktif) dan soft delete sebelum produksi.

## P1 — penting untuk operasional

### 6. Arti kolom lama

Sudah diputuskan:

| Kolom | Keputusan |
| --- | --- |
| `SUKACITA` | Dipertahankan sebagai `families.joy_number`; akan dihubungkan dengan aplikasi lain |
| `KOMSEL` | Dipertahankan sebagai `members.cell_group`; kode seperti `KKS045` disimpan apa adanya untuk integrasi |

Masih perlu konfirmasi:

| Kolom | Dugaan | Pertanyaan |
| --- | --- | --- |
| `PELAYANAN` | Bidang pelayanan | Apakah perlu master data (Worship, Multimedia, Anak, dll.)? |
| `KETERANGAN` | Catatan bebas | Apakah perlu ditampilkan ke jemaat? |
| `USERINPUT` | Email operator | Tidak diimpor; apakah masih relevan? |
| `PHOTO` / `PHOTO KELUARGA` | Nama file gambar | Di mana berkas aslinya disimpan? |

### 7. Batas kategori usia

Saat ini:

- Anak 0–12
- Remaja 13–17
- Pemuda 18–30
- Dewasa 31–59
- Lansia 60+

Apakah batas ini sesuai kebijakan gereja? Batas dapat diubah di `config/church.php`.

### 8. Struktur wilayah — **diputuskan**

Alamat memakai data wilayah berjenjang dari `database/data/kodepos.csv`:

- provinsi,
- kabupaten/kota,
- kecamatan,
- kelurahan/desa,
- kode pos.

Semua wajib diisi. Data disimpan sebagai teks pada `families` agar kompatibel dengan data lama, tetapi form dan validasi memakai tabel `regions`. Impor dijalankan dengan `php artisan regions:import` atau otomatis lewat `migrate --seed`. Detail: [REGIONS.md](REGIONS.md).

### 9. Format kartu KKJ

Sudah diputuskan:

- ukuran **A4 landscape**, satu halaman per keluarga,
- QR berisi **tautan verifikasi publik** (`/verifikasi/{token}`) yang membuka ringkasan keluarga tanpa login,
- logo resmi dapat diunggah admin lewat **Pengaturan Gereja**; tanpa logo dipakai monogram salib,
- nama penanda tangan, jabatan penanda tangan, dan catatan kartu dapat diubah lewat **Pengaturan Gereja**.

Halaman verifikasi hanya menampilkan nomor KKJ, nama kepala keluarga, jumlah anggota, wilayah, dan tahun masuk; NIK, tanggal lahir, alamat lengkap, dan kontak tidak ditampilkan.

Masih perlu konfirmasi:

- apakah NIK boleh dicetak di kartu (saat ini tidak; kartu hanya memuat telepon),
- masa berlaku kartu atau kebutuhan cetak ulang.

### 10. Pembuatan akun dan reset kata sandi

Saat ini admin membuat akun secara manual; belum ada reset mandiri.

**Pilihan:**

- admin membuat semua akun *(rekomendasi)*,
- jemaat mendaftar sendiri lalu disetujui admin,
- reset kata sandi via email,
- reset oleh admin saja.

Jika memakai email, perlu konfigurasi SMTP.

### 11. Ekspor, cetak massal, dan audit

Apakah diperlukan:

- ekspor Excel/CSV,
- cetak kartu KKJ untuk banyak keluarga sekaligus,
- riwayat perubahan data (siapa mengubah apa dan kapan),
- log akses/download PDF?

**Rekomendasi:** audit log dan cetak massal masuk fase berikutnya.

## P2 — pengembangan lanjutan

### 12. Nomor KKJ dan KTJ — **sebagian diputuskan**

- Nomor KKJ baru dibuat otomatis: 5 karakter kode gereja + `00` + 2 digit tahun + 4 digit urutan (`0001`–`9999`), reset setiap awal tahun.
- Kode gereja diatur admin saat pertama kali memakai aplikasi dan terkunci setelah dipakai.
- Nomor KKJ lama hasil impor tidak diubah.
- Nomor KKJ tidak dapat diubah dari form setelah dibuat.
- Nomor KTJ baru dibuat otomatis 15 karakter: 13 karakter nomor KKJ + kode anggota (`0` untuk kepala keluarga/pasangan, `1`–`9` untuk anggota keluarga sesuai anak ke-) + kode jenis kelamin (`0` pria, `1` wanita).
- Nomor KTJ ditampilkan pada formulir dan halaman detail jemaat. Akses jemaat ke nomor KTJ sendiri masih dapat ditinjau pada fase berikutnya.

### 13. Perubahan status keluarga

Bagaimana menangani:

- pernikahan,
- perceraian,
- kematian,
- pindah gereja,
- pemisahan KKJ (anak menikah),
- anggota baru (bayi).

Apakah perlu riwayat, atau cukup edit data saat ini?

### 14. Duplikasi data

Apakah perlu deteksi duplikat berdasarkan:

- nama + tanggal lahir,
- NIK,
- nomor telepon,
- kombinasi KKJ + nama?

Saat ini belum ada validasi duplikat selain `KTJID` unik.

### 15. Notifikasi

Apakah aplikasi perlu:

- pengingat ulang tahun,
- pengingat follow-up data belum lengkap,
- notifikasi WhatsApp/email?

Saat ini hanya ada daftar ulang tahun 30 hari di dashboard, tanpa pengiriman pesan.

### 16. Deployment

Perlu diketahui:

- hosting: VPS, shared hosting, atau cloud?
- database: MySQL/MariaDB versi berapa?
- domain dan HTTPS,
- siapa yang mengelola backup,
- apakah aplikasi diakses publik atau hanya jaringan gereja.

### 17. Multi-gereja

Apakah aplikasi hanya untuk satu gereja, atau perlu mendukung beberapa lokasi/gereja dalam satu instalasi?

Saat ini satu instalasi = satu gereja.

## Jawaban minimum untuk lanjut

Keputusan struktur utama sudah lengkap. Jika waktu terbatas, cukup jawab lima hal ini:

1. Siapa yang berwenang mengoreksi data dan bagaimana alur perbaikan 112 WhatsApp, 10 NIK, dan 2 email yang tidak valid?
2. Apakah perlu status jemaat (aktif, pindah, meninggal, nonaktif) dan soft delete sebelum produksi?
3. Apakah `PELAYANAN` perlu master data, dan apakah `KETERANGAN` boleh dilihat jemaat?
4. Apakah NIK boleh dicetak pada kartu KKJ dan berapa masa berlaku kartu? Logo, nama penanda tangan, serta jabatan penanda tangan sudah dapat diatur di **Pengaturan Gereja**.
5. Domain HTTPS produksi (untuk `APP_URL`) agar tautan QR dapat dibuka dari luar jaringan gereja.
