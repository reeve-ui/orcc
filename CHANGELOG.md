# Changelog

Semua perubahan penting pada ORCC4CHURCH didokumentasikan di file ini.

Format versi mengikuti [Semantic Versioning](https://semver.org/lang/id/):
`MAJOR.MINOR.PATCH`. Riwayat perubahan mengikuti struktur
[Keep a Changelog](https://keepachangelog.com/id/1.1.0/).

## [1.1.0] - 2026-11-15

### Ditambahkan

- Tombol **Pembaruan aplikasi** khusus admin di Pengaturan Gereja untuk
  memeriksa commit terbaru di GitHub dan menjalankan pembaruan tanpa login
  ke server.
- Pemeriksaan pembaruan menggunakan `git ls-remote` dan perbandingan commit,
  dengan status terpasang, commit GitHub, serta tautan commit.
- Proses pembaruan di latar belakang: `git pull --ff-only`, Composer,
  migrasi database, build aset Vite, dan pembersihan cache.
- Perintah `orcc4church:update` untuk pembaruan manual lewat SSH.
- Status pembaruan di `storage/app/private/update-status.json` dan log di
  `storage/logs/update.log`.
- Konfigurasi `APP_UPDATE_*` untuk mengatur repositori, branch, binary, dan
  build aset.
- Dokumentasi pembaruan di [docs/UPDATES.md](docs/UPDATES.md).

### Diubah

- Versi aplikasi sekarang dibaca dari file `VERSION` di root proyek.
  `APP_VERSION` di `.env` hanya menjadi cadangan bila file tersebut tidak ada.
- Instruksi instalasi memakai repositori resmi
  `https://github.com/reeve-ui/orcc.git`.
- Halaman Pengaturan Gereja menampilkan versi aplikasi dan status pembaruan.

### Catatan

- Pembaruan otomatis memerlukan instalasi hasil `git clone`, working tree
  bersih, Git, PHP CLI, Composer, Node.js/npm, fungsi `exec`/`proc_open`, dan
  akses tulis ke folder proyek.
- Backup database dan folder `storage/app/public` sebelum memperbarui.

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
