# Pemetaan Data Excel → Database

Sumber: workbook ORCCDATA milik gereja (berkas contoh tidak disertakan dalam repositori)
Perintah: `php artisan orccdata:import`
Antarmuka: **Pengaturan Gereja → Impor data dari Excel** (khusus admin)

Dokumen ini menjelaskan bagaimana workbook lama dipetakan ke tabel `families` dan `members`, nilai apa yang dinormalisasi, dan anomali apa yang sengaja tidak ditebak.

## 1. Struktur workbook

| Sheet | Peran | Baris data | Kunci |
| --- | --- | ---: | --- |
| `KKJ` | Lapisan utama — satu baris per keluarga | 169 | `KKJID` |
| `JEMAAT` | Lapisan kedua — satu baris per anggota | 455 | `KTJID`, relasi `KKJID` |

Pada workbook contoh yang dipakai saat pengembangan (tidak disertakan):

- Tidak ada `KKJID` duplikat.
- Tidak ada `KTJID` duplikat.
- Tidak ada anggota tanpa keluarga (orphan).
- Setiap anggota memiliki `KKJID` dan `NAMA LENGKAP`.

Jumlah baris pada workbook contoh adalah 169 KKJ dan 455 jemaat. Repositori ini tidak menyertakan workbook maupun data KKJ/jemaat contoh; database hasil instalasi baru dimulai kosong untuk data keluarga dan jemaat.

## 2. Pemetaan sheet `KKJ` → `families`

| Kolom Excel | Kolom database | Catatan |
| --- | --- | --- |
| `KKJID` | `kkj_id` | Unik; dipakai sebagai kunci impor dan nomor pada kartu |
| `KEPALA KELUARGA` | `head_name` | Jika kosong, diisi `Keluarga {KKJID}` lalu diperbaiki dari anggota berstatus kepala keluarga |
| `MASUK` | `joined_year` | Hanya angka 1–4 digit; 2 digit dianggap tahun 2000-an; di luar 1900–tahun depan ditolak |
| `SUKACITA` | `joy_number` | Disimpan sebagai teks; pada data contoh 22 terisi, 18 di antaranya berupa angka |
| `PROVINSI` | `province` | Teks |
| `KABUPATEN KOTA` | `city` | Teks |
| `KECAMATAN` | `district` | Teks; dipakai untuk filter dan statistik wilayah |
| `KELURAHAN` | `village` | Teks |
| `KODE POS` | `postal_code` | Teks; 71 terisi |
| `ALAMAT TINGGAL` | `address` | Teks; 157 terisi |
| `PHOTO KELUARGA` | `photo_path` | Nama file dari workbook, bukan file gambar yang ikut terimpor |
| `QRCODE` | `qr_payload` | Hanya disimpan jika mengandung `http`; QR pada kartu baru dibuat dari tautan verifikasi publik, bukan dari kolom ini |
| `USERINPUT` | — | Tidak diimpor; berisi email operator, bukan atribut jemaat |

Catatan: `USERINPUT` bukan data keluarga. Nilainya sengaja tidak dipakai agar tidak mengotori data jemaat.

Nomor `KKJID` lama dipertahankan apa adanya. KKJ baru yang dibuat lewat aplikasi mendapat nomor otomatis dengan format `{kode gereja 5}{00}{tahun 2}{urutan 4}`, misalnya `SEKRT00260001`. Urutan kembali ke `0001` setiap awal tahun. Kode gereja diatur admin di menu **Pengaturan Gereja** dan terkunci setelah dipakai.

Alamat hasil impor masih berupa teks bebas. Saat KKJ diubah, form meminta alamat dipilih ulang dari data wilayah berjenjang (provinsi → kabupaten/kota → kecamatan → kelurahan → kode pos) agar konsisten dengan `database/data/kodepos.csv`.

## 3. Pemetaan sheet `JEMAAT` → `members`

| Kolom Excel | Kolom database | Catatan |
| --- | --- | --- |
| `KKJID` | `family_id` | Dicari ke `families.kkj_id`; jika belum ada, keluarga placeholder dibuat |
| `KTJID` | `ktj_id` | Unik; kunci impor utama anggota. Form aplikasi membuat KTJ otomatis 15 karakter: 13 karakter KKJ + kode anggota + kode jenis kelamin |
| `STATUS KELUARGA` | `family_status` | Harus salah satu: Kepala Keluarga, Pasangan, Anak, Saudara, Anggota Keluarga |
| `ANAK KE` | `child_order` | Hanya 1–30; dipakai untuk urutan anak |
| `JENIS KELAMIN` | `gender` | `Pria` / `Wanita`; lihat normalisasi di bawah |
| `NAMA LENGKAP` | `full_name` | Wajib; baris tanpa nama dilewati |
| `ALIAS` | `alias` | Teks |
| `NOMOR KTP` | `nik` | Hanya 16 digit angka; selain itu dikosongkan dan ditandai |
| `SELULER` | `phone` | Dibersihkan; 8–15 digit |
| `WHATSAPP` | `whatsapp` | Dibersihkan; 8–15 digit |
| `EMAIL` | `email` | Harus lolos validasi email; disimpan huruf kecil |
| `TANGGAL LAHIR` | `birth_date` | Serial Excel atau tanggal teks; disimpan sebagai `date` |
| `TANGGAL BAPTIS` | `baptism_date` | Opsional; serial Excel atau tanggal teks, tidak boleh di masa depan |
| `TEMPAT BAPTIS` | `baptism_place` | Opsional; teks nama gereja/tempat baptisan |
| `STATUS PERKAWINAN` | `marital_status` | Harus salah satu: Belum Menikah, Telah Menikah, Pernah Menikah. Nilai lama `Sudah Menikah` tetap diterima dan dipetakan ke `Telah Menikah`. |
| `NAMA PASANGAN` | `spouse_name` | Teks bermakna; kode angka murni ditolak |
| `PHOTO` | `photo_path` | Nama file/default dari workbook, bukan berkas gambar |
| `QRCODE` | `qr_payload` | Hanya disimpan jika mengandung `http` |
| `PELAYANAN` | `ministry` | Teks bermakna; kode angka murni ditolak |
| `KOMSEL` | `cell_group` | Disimpan apa adanya, termasuk kode seperti `KKS045` atau kode angka, karena akan dihubungkan dengan aplikasi lain |
| `KETERANGAN` | `notes` | Teks bermakna; kode angka murni ditolak |
| `USERINPUT` | — | Tidak diimpor |

## 4. Normalisasi nilai

### Gender

Urutan penentuan:

1. `PRIA`, `LAKI-LAKI`, `LAKI LAKI`, `L` → `Pria`
2. `WANITA`, `PEREMPUAN`, `P` → `Wanita`
3. Kode lama `668` → `Pria`, `681` → `Wanita` (dukungan kompatibilitas; tidak ditemukan di workbook contoh)
4. Selain itu → kosong + flag `jenis kelamin tidak dikenali`

Pada workbook contoh: 454 dari 455 anggota memiliki gender; 1 kosong.

### Tanggal lahir

- Angka diperlakukan sebagai serial Excel, lalu dikonversi ke tanggal.
- Teks diurai dengan Carbon.
- Ditolak jika sebelum 1900, di masa depan, atau berusia lebih dari 110 tahun.
- Pada workbook contoh: 452 terisi, 3 kosong, tidak ada yang ditolak.

### Tanggal baptis

- Sama seperti tanggal lahir: angka diperlakukan sebagai serial Excel, teks diurai dengan Carbon.
- Opsional; dikosongkan jika tidak ada. Tanggal di masa depan atau sebelum 1900 ditolak dan dicatat sebagai flag `tanggal baptis tidak valid`.
- `TEMPAT BAPTIS` disimpan sebagai teks; nilai yang hanya berupa kode angka murni ditolak dan dicatat sebagai flag.

### Telepon / WhatsApp

- Karakter selain angka dan `+` dibuang.
- Harus 8–15 digit.
- Nilai seperti `62` (hanya kode negara) dianggap tidak valid.
- Pada data contoh, 112 nomor WhatsApp ditandai tidak valid.

### NIK

- Harus tepat 16 digit angka.
- Nilai seperti `12345`, `123456789012345` (15 digit), atau teks lain dikosongkan.
- Pada data contoh, 10 NIK ditandai tidak valid.

### Email

- Harus lolos `FILTER_VALIDATE_EMAIL`.
- Contoh yang ditolak: `email@example.com` (placeholder), `nama@example com` (spasi).
- Pada data contoh, 2 email ditandai tidak valid.

### Teks bermakna

Untuk `NAMA PASANGAN`, `PELAYANAN`, `KOMSEL`, dan `KETERANGAN`, nilai yang hanya berupa 1–6 digit dianggap kode internal, bukan teks. Nilainya dikosongkan dan dicatat sebagai flag.

## 5. Data yang tidak diimpor

- `USERINPUT` — email operator, bukan atribut jemaat.
- Berkas foto — workbook hanya menyimpan nama file (`default-man.png`, `familychild.png`, path `KKJ_Images/...`), bukan gambar. Foto akan tampil jika file benar-benar diunggah ke `storage/app/public/...`. Bila keluarga belum memiliki foto, aplikasi otomatis memakai `public/images/family-default.png`.
- QR lama dari QuickChart/Google Charts — tidak dipakai ulang; QR baru dibuat dari tautan verifikasi publik `/verifikasi/{token}`.
- `KETERANGAN` yang berupa kode angka — ditolak seperti teks bermakna lainnya.

## 6. Sifat impor

- **Non-destruktif**: hanya mengisi kolom yang masih kosong pada record yang sudah ada.
- **Idempoten**: KKJ dicocokkan lewat `KKJID`, anggota lewat `KTJID`; menjalankan ulang tidak menggandakan data.
- **Transaksional**: semua perubahan dibatalkan jika satu baris gagal.
- **`--fresh`**: menghapus seluruh KKJ dan jemaat lebih dulu, dengan konfirmasi.
- **`--dry-run`**: menjalankan seluruh proses lalu rollback.
- **`legacy_raw`**: menyimpan salinan baris asli untuk audit dan penelusuran.
- **`data_flags`**: menyimpan catatan kualitas data per anggota baru.

## 7. Impor lewat aplikasi

Admin dapat mengimpor dari **Pengaturan Gereja → Impor data dari Excel**. Antarmuka ini memakai `App\Services\OrccdataImporter` yang sama dengan perintah Artisan, sehingga aturan pemetaan, normalisasi, transaksi, dan flag tetap identik.

Langkah:

1. Buka **Pengaturan Gereja**.
2. Klik **Unduh template** untuk mendapatkan file `.xlsx` dengan sheet `KKJ`, `JEMAAT`, dan `PETUNJUK`.
3. Isi sheet `KKJ` dan `JEMAAT`; jangan mengubah nama sheet atau header kolom.
4. Unggah file `.xlsx`/`.xls` (maksimal 20 MB).
5. Pilih **Uji coba (dry-run)** bila ingin memeriksa hasil tanpa menyimpan.
6. Jalankan **Impor Data**.

Opsi tambahan:

- **Impor fresh** menghapus seluruh KKJ dan jemaat sebelum impor. Opsi ini memerlukan centang konfirmasi dan juga konfirmasi JavaScript.
- **Dry-run** memproses seluruh file lalu rollback; hasil tetap ditampilkan, tetapi database tidak berubah.
- Setelah selesai, halaman pengaturan menampilkan jumlah KKJ/jemaat baru, yang diperbarui, baris yang dilewati, serta catatan kualitas data.

Template hanya berisi header dan petunjuk, bukan data contoh yang akan ikut terimpor. Sheet `PETUNJUK` tidak dibaca oleh importer. Template dibuat saat diunduh, jadi tidak perlu menyimpan file biner di repositori.

## 8. Anomali workbook contoh

| Temuan | Jumlah | Penanganan |
| --- | ---: | --- |
| Nomor WhatsApp tidak valid | 112 | Dikosongkan, dicatat di `data_flags` |
| NIK bukan 16 digit | 10 | Dikosongkan, dicatat |
| Email tidak valid | 2 | Dikosongkan, dicatat |
| Tanggal lahir kosong | 3 | Dibiarkan kosong; masuk kategori usia “Belum diketahui” |
| Gender kosong | 1 | Dibiarkan kosong; ditampilkan “—” |
| `SUKACITA` berupa angka | 18 dari 22 | Disimpan sebagai teks pada `joy_number` |
| `USERINPUT` berisi email operator | 169 + 421 | Tidak diimpor |
| `PHOTO`/`PHOTO KELUARGA` hanya nama file | 169 + 421 | Tidak ada gambar yang diunggah otomatis |
| `KETERANGAN` hampir seluruhnya kosong | 1 terisi | Diimpor sebagai `notes` |

## 9. Rekomendasi tindak lanjut data

1. Minta sekretariat memverifikasi 112 nomor WhatsApp dan 10 NIK.
2. Lengkapi 3 tanggal lahir dan 1 gender yang kosong.
3. Konfirmasi arti `SUKACITA` — apakah nomor sukacita, nomor lama, atau kode lain.
4. Konfirmasi apakah `PELAYANAN` dan `KOMSEL` perlu menjadi entitas/master data tersendiri.
5. Kumpulkan berkas foto asli jika foto ingin ditampilkan di aplikasi dan kartu.
6. Saat mengubah KKJ lama, pilih ulang alamat dari data wilayah berjenjang agar kota/kecamatan/kelurahan/kode pos sesuai `kodepos.csv`. Lihat [REGIONS.md](REGIONS.md).
