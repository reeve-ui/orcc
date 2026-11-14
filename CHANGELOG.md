# Changelog

Semua perubahan penting pada ORCC4CHURCH didokumentasikan di file ini.

Format versi mengikuti [Semantic Versioning](https://semver.org/lang/id/):
`MAJOR.MINOR.PATCH`. Riwayat perubahan mengikuti struktur
[Keep a Changelog](https://keepachangelog.com/id/1.1.0/).

## [1.0.2] - 2026-11-12

### Diperbaiki

- Logo JPG/PNG yang diunggah sekarang otomatis diperkecil menjadi maksimal
  512 × 512 px dengan rasio tetap, lalu disimpan ulang sebagai PNG.
- Logo lama baru dihapus setelah logo baru berhasil diproses dan disimpan,
  sehingga logo sebelumnya tidak hilang jika unggahan gagal.

## [1.0.1] - 2026-11-12

### Diperbaiki

- Login sekarang menormalisasi email menjadi huruf kecil dan tetap menemukan
  akun lama yang emailnya tersimpan dengan huruf besar.
- Email pengguna baru dinormalisasi otomatis saat disimpan.

### Ditambahkan

- Perintah `orcc4church:reset-password` untuk mengatur ulang kata sandi pengguna
  dari server, dengan opsi `--activate` untuk mengaktifkan akun nonaktif.

## [1.0.0] - 2026-11-11

### Ditambahkan

- Fitur reset seluruh data KKJ dan jemaat khusus admin, dengan konfirmasi teks
  `RESET DATA` dan centang pernyataan.
- Penghapusan foto keluarga/jemaat dan reset urutan nomor KKJ saat reset data.
- Versi aplikasi yang dapat diatur melalui `APP_VERSION` dan ditampilkan pada
  antarmuka.
- Lisensi open source MIT.

### Diubah

- Nama aplikasi menjadi **ORCC4CHURCH**.
- Istilah dan nama file impor diseragamkan menjadi `orccdata`.
- Perintah impor sekarang menggunakan `orccdata:import`.
- Path workbook default berubah menjadi `storage/app/import/orccdata.xlsx`.
- Template impor berubah menjadi `template-impor-orccdata.xlsx`.
- Dokumen README, peran akses, dan pemetaan data disesuaikan dengan nama baru.

### Dipertahankan

- Identitas gereja tetap dapat diubah melalui Pengaturan Gereja.
- Akun pengguna, pengaturan gereja, dan data wilayah tidak dihapus saat reset.
- Impor Excel, peran akses, kartu KKJ PDF, verifikasi publik, dan data wilayah
  tetap tersedia.
