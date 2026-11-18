# Pembaruan aplikasi

ORCC4CHURCH menyediakan tombol pembaruan khusus admin di **Pengaturan Gereja**. Tombol ini membandingkan commit yang terpasang dengan branch `main` di GitHub, lalu menjalankan langkah pembaruan di server.

Fitur ini dirancang untuk instalasi yang dibuat dengan `git clone`, bukan unduhan ZIP.

## Prasyarat

- Instalasi berupa repositori Git.
- Remote `origin` mengarah ke repositori resmi, misalnya `https://github.com/reeve-ui/orcc.git`.
- Working tree bersih. File yang diabaikan Git seperti `.env`, database SQLite, `vendor/`, `node_modules/`, dan upload pengguna tidak dihitung sebagai perubahan.
- Web server dapat menjalankan:
  - Git,
  - PHP CLI,
  - Composer,
  - npm dan Node.js.
- Web server memiliki akses tulis ke folder proyek dan folder `.git`.
- Fungsi PHP `exec` dan `proc_open` tidak dinonaktifkan.
- Koneksi internet dari server ke GitHub tersedia.

Jika server tidak memenuhi syarat tersebut, gunakan perintah CLI lewat SSH.

## Alur di antarmuka

1. Login sebagai admin.
2. Buka **Pengaturan Gereja**.
3. Pada kartu **Pembaruan aplikasi**, klik **Cek pembaruan**.
4. Aplikasi menampilkan commit terpasang, commit GitHub, dan statusnya.
5. Jika tersedia pembaruan dan tidak ada perubahan pada file yang dilacak Git, klik **Update sekarang**.
6. Konfirmasi dialog, lalu tunggu sampai status berubah menjadi berhasil atau gagal. Halaman akan memantau status secara otomatis.
7. Setelah berhasil, klik **Muat ulang halaman** agar versi baru ditampilkan.

Pemeriksaan commit disimpan sementara selama 5 menit. Tombol **Cek pembaruan** selalu memaksa pemeriksaan baru.

## Langkah yang dijalankan

Proses pembaruan berjalan di latar belakang dan menjalankan urutan berikut:

```text
git pull --ff-only origin main
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
php artisan migrate --force
npm ci
npm run build
php artisan optimize:clear
```

Catatan:

- `git pull --ff-only` menolak merge yang membutuhkan commit lokal atau konflik.
- `composer install --no-dev` menghapus dependensi pengembangan. Ini cocok untuk produksi.
- `npm ci` memakai `package-lock.json`; `npm run build` membangun aset Vite.
- Kegagalan `npm ci` atau `npm run build` dicatat sebagai peringatan, tetapi pembaruan kode tetap dianggap selesai. Jalankan build manual jika perlu.
- Pembaruan tidak mengaktifkan mode maintenance. Lakukan saat aplikasi tidak sedang dipakai.

## Konfigurasi

Tambahkan atau sesuaikan nilai berikut di `.env`:

```dotenv
APP_UPDATE_ENABLED=true
APP_UPDATE_REPOSITORY=reeve-ui/orcc
APP_UPDATE_BRANCH=main
APP_UPDATE_BUILD_ASSETS=true
APP_UPDATE_TIMEOUT=900

# Opsional: path absolut bila binary tidak ada di PATH web server.
# APP_UPDATE_PHP_BINARY=/usr/bin/php
# APP_UPDATE_GIT_BINARY=/usr/bin/git
# APP_UPDATE_COMPOSER_BINARY=/usr/local/bin/composer
# APP_UPDATE_NPM_BINARY=/usr/bin/npm
```

Keterangan:

| Variabel | Arti |
| --- | --- |
| `APP_UPDATE_ENABLED` | Tampilkan kartu dan tombol pembaruan di Pengaturan Gereja. |
| `APP_UPDATE_REPOSITORY` | Repositori GitHub yang dipakai untuk tautan commit. |
| `APP_UPDATE_BRANCH` | Branch yang diperiksa dan ditarik. |
| `APP_UPDATE_BUILD_ASSETS` | Jalankan `npm ci` dan `npm run build`. Set `false` bila aset dibangun di tempat lain. |
| `APP_UPDATE_TIMEOUT` | Batas waktu (detik) untuk langkah Composer dan proses update. |
| `APP_UPDATE_PHP_BINARY` | Path PHP CLI. Di web SAPI, nilai default `php`. |
| `APP_UPDATE_GIT_BINARY` | Path binary Git. |
| `APP_UPDATE_COMPOSER_BINARY` | Path binary Composer. |
| `APP_UPDATE_NPM_BINARY` | Path binary npm. |

Setelah mengubah konfigurasi, jalankan:

```bash
php artisan optimize:clear
```

Untuk menonaktifkan tombol pembaruan:

```dotenv
APP_UPDATE_ENABLED=false
```

## Perintah CLI

Jika `exec` dinonaktifkan atau web server tidak dapat menulis ke repositori, jalankan pembaruan lewat SSH:

```bash
php artisan orcc4church:update
```

Perintah CLI tetap memerlukan `proc_open` karena langkah internal memakai komponen Process Laravel. Jika `proc_open` juga dinonaktifkan, jalankan langkah pembaruan secara manual.

Perintah ini menjalankan langkah yang sama seperti tombol pembaruan. Opsi `--user` hanya untuk mencatat admin yang memulai:

```bash
php artisan orcc4church:update --user=1
```

## Status dan log

- Status terakhir: `storage/app/private/update-status.json`
- Log proses: `storage/logs/update.log`
- Log aplikasi: `storage/logs/laravel.log`

Status berisi langkah terakhir, pesan, keluaran perintah, dan peringatan. File tersebut berada di folder privat dan tidak diakses publik.

## Keamanan dan batasan

- Hanya admin yang dapat membuka halaman, memeriksa, dan menjalankan pembaruan.
- Permintaan cek dan update menggunakan CSRF; semua rute menggunakan throttle.
- Proses memakai lock agar dua pembaruan tidak berjalan bersamaan.
- Pembaruan ditolak bila folder bukan repositori Git, ada perubahan pada file yang dilacak Git, commit lokal lebih baru, riwayat berbeda, atau tidak ada pembaruan.
- File yang tidak dilacak Git (misalnya ZIP atau file sementara di folder aplikasi) tidak menghalangi pembaruan. Jika file tersebut bentrok dengan file baru dari GitHub, `git pull` akan berhenti dan menampilkan pesan yang jelas.
- Repositori harus publik atau kredensial Git harus sudah tersedia untuk web server.
- Pembaruan tidak mengubah `.env` atau file upload. Migrasi database dapat mengubah skema; backup database sebelum memperbarui.
- Pembaruan tidak membuat backup otomatis. Backup database dan `storage/app/public` sebelum memperbarui.

## Pemecahan masalah

### Tombol pembaruan tidak muncul

- Pastikan `APP_UPDATE_ENABLED=true`.
- Pastikan login sebagai admin.
- Jalankan `php artisan optimize:clear` setelah mengubah `.env`.

### "Folder aplikasi bukan repositori Git"

Instalasi berasal dari ZIP, bukan `git clone`. Ubah menjadi clone Git atau perbarui secara manual.

### "Ada perubahan file lokal"

Ada file yang dilacak Git dan sudah diubah. Halaman Pengaturan Gereja menampilkan daftar file yang berubah. Jalankan perintah berikut di folder aplikasi:

```bash
git status --porcelain --untracked-files=no
```

Lalu commit, stash, atau kembalikan perubahan tersebut:

```bash
git stash push -m "perubahan sebelum update"
# atau
git restore path/ke/file
```

File yang tidak dilacak Git tidak menghalangi pembaruan. Jangan menghapus file pengguna yang tidak dilacak Git.

### "Git tidak tersedia" atau "Composer gagal"

- Pasang Git/Composer/npm.
- Set path absolut melalui `APP_UPDATE_*_BINARY`.
- Pastikan binary dapat dijalankan oleh user web server.

### "Fungsi exec dinonaktifkan"

Jalankan `php artisan orcc4church:update` lewat SSH, atau aktifkan `exec` di `php.ini`.

### "Fungsi proc_open dinonaktifkan"

Aktifkan `proc_open` di `php.ini` atau jalankan langkah pembaruan secara manual lewat SSH.

### "PHP CLI tidak dapat dijalankan"

Set `APP_UPDATE_PHP_BINARY` ke path PHP CLI yang benar, misalnya `/usr/bin/php8.4`.

### Pemeriksaan pembaruan timeout

Pemeriksaan menjalankan `git ls-remote` dari request web. Naikkan `max_execution_time` PHP (misalnya menjadi 60 detik) atau jalankan pembaruan lewat CLI.

### Update berhasil tetapi tampilan lama

Jalankan:

```bash
php artisan optimize:clear
npm run build
```

Lalu muat ulang halaman dengan cache browser dibersihkan.

Jika server memakai OPcache dengan `opcache.validate_timestamps=0`, kode baru tidak akan dimuat sampai PHP-FPM di-reload. Reload PHP-FPM atau set `opcache.validate_timestamps=1`.

### Update gagal setelah `git pull`

Periksa `storage/logs/update.log`. Jika perlu rollback:

```bash
git reset --hard <commit-sebelumnya>
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
npm ci && npm run build
php artisan optimize:clear
```

Pulihkan database dari backup bila migrasi sudah berjalan. Rollback manual hanya dilakukan oleh operator yang memahami risikonya.

## Checklist rilis untuk pengelola

1. Ubah kode dan uji.
2. Perbarui `VERSION` (misalnya `1.2.0`).
3. Catat perubahan di `CHANGELOG.md`.
4. Commit dan push ke branch `main` di GitHub.
5. Pengguna membuka **Pengaturan Gereja** dan klik **Cek pembaruan**.
6. Pengguna klik **Update sekarang** setelah backup.

Jangan mengunggah `.env`, `database/database.sqlite`, workbook Excel berisi data jemaat, atau file upload ke repositori publik.
