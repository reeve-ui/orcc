# ORCC4CHURCH

ORCC4CHURCH adalah aplikasi web untuk membantu sekretariat gereja mengelola **Kartu Keluarga Jemaat (KKJ)**, data jemaat, dashboard demografi, dan kartu KKJ dalam format PDF.

Aplikasi ini dibangun dengan Laravel 12, Blade, Tailwind CSS, Alpine.js, dan Vite. Identitas gereja, logo, kode penomoran KKJ, serta catatan kartu dapat diubah dari aplikasi tanpa mengedit kode.

**Versi saat ini:** 1.0.2 · [Riwayat perubahan](CHANGELOG.md)

> **Tidak ada data contoh KKJ/jemaat di repositori ini.** Setelah instalasi, database dimulai kosong untuk data keluarga dan jemaat. Isi data melalui formulir atau impor workbook Excel milik gereja Anda.

## Daftar isi

- [Fitur](#fitur)
- [Teknologi](#teknologi)
- [Persyaratan](#persyaratan)
- [Instalasi](#instalasi)
- [Login pertama](#login-pertama)
- [Impor data Excel](#impor-data-excel)
- [Peran dan akses](#peran-dan-akses)
- [Konfigurasi](#konfigurasi)
- [Versi dan changelog](#versi-dan-changelog)
- [Data wilayah](#data-wilayah)
- [Pengujian](#pengujian)
- [Catatan produksi](#catatan-produksi)
- [Struktur proyek](#struktur-proyek)
- [Pemecahan masalah](#pemecahan-masalah)
- [Lisensi](#lisensi)

## Fitur

- **Data KKJ** — tambah, lihat, ubah, hapus, cari, dan filter keluarga. Nomor KKJ dibuat otomatis dari kode gereja, tahun, dan urutan keluarga.
- **Data jemaat** — setiap anggota terhubung ke satu KKJ. Pencarian berdasarkan nama, KTJ, NIK, telepon, atau nomor KKJ; filter status, gender, dan kategori usia.
- **Alamat berjenjang** — provinsi → kabupaten/kota → kecamatan → kelurahan → kode pos, bersumber dari `database/data/kodepos.csv`.
- **Pengaturan gereja** — admin dapat mengubah identitas gereja, kode penomoran KKJ, catatan kartu, nama dan jabatan penanda tangan, serta logo.
- **Reset data** — admin dapat menghapus seluruh data KKJ, jemaat, foto, dan urutan nomor KKJ dengan konfirmasi teks `RESET DATA` serta centang pernyataan. Akun pengguna, pengaturan gereja, dan data wilayah tetap dipertahankan.
- **Dashboard** — total KKJ, total jemaat, komposisi gender, kategori usia, status pernikahan, sebaran wilayah, ulang tahun terdekat, dan KKJ terbaru.
- **Kartu KKJ PDF** — satu halaman A4 landscape per keluarga, berisi identitas kepala keluarga, alamat, tabel anggota, QR code, logo gereja, catatan, serta nama dan jabatan penanda tangan.
- **Verifikasi publik** — QR pada kartu membuka halaman ringkasan keluarga tanpa login. Halaman ini tidak menampilkan NIK, tanggal lahir, alamat lengkap, atau kontak.
- **Foto keluarga** — memakai foto unggahan bila tersedia; tanpa unggahan, aplikasi memakai foto default.
- **Empat peran** — `admin`, `leader`, `pengerja`, dan `jemaat` dengan batas akses berjenjang.
- **Dashboard jemaat** — jemaat hanya melihat kartu keluarga dan profilnya sendiri.
- **Dark mode & mobile** — tema gelap/terang, sidebar responsif, dan tabel yang berubah menjadi daftar kartu di layar kecil.
- **Impor Excel** — unggah workbook dari **Pengaturan Gereja**, unduh template siap isi, uji dengan mode dry-run, dan impor ulang dengan opsi fresh.
- **Halaman error** — 403, 404, 419, 429, 500, dan 503 dengan tampilan yang konsisten.

## Teknologi

| Bagian | Teknologi |
| --- | --- |
| Framework | Laravel 12 |
| Bahasa | PHP ^8.2 |
| Tampilan | Blade, Tailwind CSS 3.4, Alpine.js |
| Build | Vite 6 |
| PDF | barryvdh/laravel-dompdf |
| QR code | bacon/bacon-qr-code |
| Excel | PhpSpreadsheet 5 |
| Database | MySQL/MariaDB (produksi), SQLite (pengembangan) |
| Autentikasi | Session auth bawaan Laravel |

## Persyaratan

- PHP **8.2 atau lebih baru**
- Composer 2
- Node.js **20 atau lebih baru** dan npm
- Ekstensi PHP: `dom`, `fileinfo`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `zip`
- Ekstensi database: `pdo_sqlite` untuk SQLite, atau `pdo_mysql` untuk MySQL/MariaDB
- Ekstensi tambahan yang disarankan: `gd`, `intl`

Periksa ekstensi yang aktif:

```bash
php -m
```

## Instalasi

### 1. Clone repositori

```bash
git clone https://github.com/USERNAME/orcc4church.git
cd orcc4church
```

Ganti `USERNAME` dengan akun GitHub pemilik repositori.

### 2. Pasang dependensi

```bash
composer install
npm install
```

### 3. Siapkan environment

```bash
cp .env.example .env
php artisan key:generate
```

Di Windows, gunakan Git Bash/WSL atau salin `.env.example` menjadi `.env` secara manual.

### 4. Pilih database

#### Opsi A — SQLite (paling cepat untuk mencoba)

Edit `.env`:

```dotenv
DB_CONNECTION=sqlite
# Hapus atau komentari DB_DATABASE agar memakai database/database.sqlite
# DB_DATABASE=
```

Buat file database:

```bash
touch database/database.sqlite
```

Di Windows PowerShell:

```powershell
New-Item database/database.sqlite -ItemType File
```

#### Opsi B — MySQL/MariaDB

Buat database:

```sql
CREATE DATABASE sekretariat_gereja
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

Sesuaikan `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sekretariat_gereja
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Migrasi, seed, dan storage link

```bash
php artisan migrate --seed
php artisan storage:link
```

`migrate --seed` akan:

- membuat tabel aplikasi;
- membuat akun admin dari nilai `ADMIN_*` di `.env`;
- mengimpor data wilayah dari `database/data/kodepos.csv` (sekitar 80.000 baris).

Proses impor wilayah dapat memakan waktu beberapa saat. Jika ingin menjalankannya terpisah:

```bash
php artisan regions:import
```

### 6. Build aset frontend

```bash
npm run build
```

Untuk pengembangan dengan hot reload, gunakan `npm run dev` di terminal terpisah.

### 7. Jalankan aplikasi

```bash
php artisan serve
```

Buka <http://localhost:8000>.

## Login pertama

Akun admin dibuat dari nilai berikut di `.env`:

```dotenv
ADMIN_NAME="Administrator"
ADMIN_EMAIL=admin@sekretariatgereja.test
ADMIN_PASSWORD=password
```

| Peran | Email | Kata sandi |
| --- | --- | --- |
| Admin | `admin@sekretariatgereja.test` | `password` |

**Ganti kata sandi admin sebelum produksi.** Tidak ada akun demo leader/pengerja/jemaat dan tidak ada data KKJ/jemaat contoh.

Login menggunakan **email**, bukan nama pengguna. Nilai `ADMIN_PASSWORD` di `.env` hanya dipakai saat akun admin pertama kali dibuat; mengubahnya setelah akun ada tidak otomatis mengubah kata sandi di database.

Jika lupa kata sandi atau akun nonaktif, reset dari server:

```bash
php artisan orcc4church:reset-password admin@sekretariatgereja.test
```

Tambahkan `--activate` jika akun juga perlu diaktifkan:

```bash
php artisan orcc4church:reset-password admin@sekretariatgereja.test --activate
```

Jika akun admin belum dibuat:

```bash
php artisan db:seed --class=AdminSeeder
```

Setelah login:

1. Buka **Pengaturan Gereja** di sidebar.
2. Isi kode gereja 5 karakter (contoh `SEKRT`), nama gereja, alamat, dan logo bila ada.
3. Simpan. Kode gereja terkunci setelah dipakai pada nomor KKJ pertama.
4. Tambah KKJ/jemaat lewat menu **Data KKJ** dan **Data Jemaat**, atau impor workbook Excel.

## Impor data Excel

Impor tersedia dari **Pengaturan Gereja → Impor data dari Excel** (khusus admin) atau lewat Artisan. Keduanya memakai implementasi yang sama di `App\Services\OrccdataImporter`.

### Impor lewat aplikasi

1. Buka **Pengaturan Gereja**.
2. Klik **Unduh template** untuk melihat nama sheet dan kolom yang dibutuhkan.
3. Isi sheet `KKJ` (satu baris per keluarga) dan `JEMAAT` (satu baris per anggota). Jangan mengubah nama sheet atau header kolom.
4. Pilih file `.xlsx` atau `.xls` (maksimal 20 MB).
5. Centang **Uji coba (dry-run)** untuk melihat hasil tanpa menyimpan, lalu klik **Impor Data**.
6. Jika hasil sudah sesuai, jalankan tanpa dry-run.

Centang **Impor fresh** hanya jika ingin menghapus seluruh KKJ dan jemaat lebih dulu. Tindakan ini memerlukan konfirmasi tambahan dan bersifat destruktif.

### Impor lewat Artisan

Letakkan workbook di `storage/app/import/orccdata.xlsx`, atau berikan path langsung:

```bash
# Uji tanpa menyimpan
php artisan orccdata:import --dry-run

# Impor dari path tertentu
php artisan orccdata:import /path/ke/workbook.xlsx --dry-run

# Impor (hanya mengisi kolom yang masih kosong)
php artisan orccdata:import /path/ke/workbook.xlsx

# Impor ulang dari nol (menghapus seluruh KKJ & jemaat lebih dulu)
php artisan orccdata:import /path/ke/workbook.xlsx --fresh
```

Workbook contoh **tidak disertakan** dalam repositori. Gunakan template dari aplikasi atau workbook milik gereja Anda.

Perilaku penting:

- Impor tidak menimpa data non-kosong yang sudah ada di database.
- Baris dengan `KKJID` atau nama kosong dilewati dan dihitung.
- Nilai yang tidak valid (NIK, telepon, email, tanggal) dikosongkan lalu dicatat di `members.data_flags`.
- Data mentah setiap baris disimpan di `legacy_raw` untuk audit.
- Seluruh proses berjalan dalam satu transaksi; jika gagal, tidak ada perubahan yang tersimpan.
- `--fresh` bersifat destruktif dan meminta konfirmasi.

Detail pemetaan kolom dan anomali ada di [docs/DATA-MAPPING.md](docs/DATA-MAPPING.md).

## Peran dan akses

| Kemampuan | Admin | Leader | Pengerja | Jemaat |
| --- | :---: | :---: | :---: | :---: |
| Lihat dashboard staf | ✓ | ✓ | ✓ | — |
| Lihat semua KKJ & jemaat | ✓ | ✓ | ✓ | — |
| Tambah/ubah KKJ & jemaat | ✓ | ✓ | ✓ | — |
| Hapus KKJ & jemaat | ✓ | ✓ | — | — |
| Unduh kartu KKJ | ✓ | ✓ | ✓ | keluarga sendiri |
| Kelola akun pengguna | ✓ | — | — | — |
| Kelola pengaturan gereja | ✓ | — | — | — |
| Reset seluruh data KKJ & jemaat | ✓ | — | — | — |
| Lihat keluarga & profil sendiri | ✓ | ✓ | ✓ | ✓ |

Peran `leader` mencakup jabatan seperti pendeta, ketua majelis, ketua yayasan, dan kepala sekretariat, dan berlaku untuk seluruh gereja (bukan per komsel).

Matriks lengkap dan catatan keamanan: [docs/ROLES.md](docs/ROLES.md).

## Konfigurasi

Nama dan versi aplikasi diatur di `.env`:

```dotenv
APP_NAME="ORCC4CHURCH"
APP_VERSION=1.0.2
```

Identitas gereja, logo, kode penomoran KKJ, catatan kartu, serta nama dan jabatan penanda tangan diubah dari menu **Pengaturan Gereja** (khusus admin). Nilainya disimpan di tabel `church_settings`, sehingga aplikasi tidak perlu diedit setelah instalasi.

Nilai `CHURCH_*` di `.env` hanya dipakai sebagai cadangan awal saat migrasi:

```dotenv
CHURCH_CODE=SEKRT
CHURCH_NAME="Sekretariat Gereja"
CHURCH_TAGLINE="Data Jemaat"
CHURCH_ADDRESS="Alamat gereja"
CHURCH_PHONE=""
CHURCH_EMAIL="sekretariat@example.com"
CHURCH_CARD_NOTE="Kartu ini diterbitkan oleh Sekretariat Gereja. Mohon simpan dengan baik."
CHURCH_CARD_SIGNATURE_NAME="Pdt. Nama, S.Th."
CHURCH_CARD_SIGNATURE="Gembala Sidang"
CHURCH_CARD_VERSION="1.0"
```

Logo disimpan di disk `public` (`storage/app/public/church`). Saat diunggah, logo JPG/PNG otomatis diperkecil menjadi maksimal 512 × 512 px dengan rasio tetap dan disimpan ulang sebagai PNG. Jalankan `php artisan storage:link` sekali agar logo dapat ditampilkan di aplikasi. Pada PDF, logo disematkan sebagai data URI sehingga tetap tampil tanpa bergantung pada symlink.

## Versi dan changelog

Versi aplikasi ditampilkan di footer, halaman masuk, halaman verifikasi publik, halaman error, dan halaman Pengaturan Gereja. Nilai versi diatur melalui `APP_VERSION` di `.env`.

Setiap perubahan yang dirilis harus dicatat di [CHANGELOG.md](CHANGELOG.md). Gunakan format Semantic Versioning:

- `MAJOR` untuk perubahan yang tidak kompatibel,
- `MINOR` untuk fitur baru yang kompatibel,
- `PATCH` untuk perbaikan bug atau perubahan kecil.

Setelah mengubah `APP_VERSION`, jalankan:

```bash
php artisan optimize:clear
```

### Format nomor KKJ

Nomor KKJ baru dibuat otomatis dengan format:

```text
{kode 5}{00}{tahun 2}{urutan 4}
```

Contoh: kode `SEKRT`, tahun 2026, keluarga pertama → `SEKRT00260001`.

- Urutan naik per keluarga baru dan kembali ke `0001` setiap awal tahun.
- Nomor KKJ lama hasil impor tidak diubah.
- Kode gereja terkunci setelah dipakai pada nomor KKJ; periksa kembali sebelum menyimpan.

### Format nomor KTJ

Nomor KTJ dibuat otomatis dari data keluarga dan jemaat:

```text
{nomor KKJ 13 karakter}{kode anggota}{kode jenis kelamin}
```

- Kode anggota `0` untuk **Kepala Keluarga** dan **Pasangan**.
- Kode anggota `1`–`9` untuk **Anggota Keluarga**, sesuai isian **Anak ke-**.
- Kode jenis kelamin `0` untuk **Pria** dan `1` untuk **Wanita**.

Contoh: KKJ `SEKRT00260001`, anak pertama laki-laki → `SEKRT0026000110`.

Pada formulir jemaat:

- **Anak ke-** hanya muncul dan wajib diisi saat status keluarga adalah **Anggota Keluarga**.
- **Jenis kelamin** wajib diisi dengan default **Pria**.
- **Status pernikahan** wajib diisi dengan default **Belum Menikah**. Isian **Nama Pasangan** hanya muncul saat status **Telah Menikah**.
- **Tanggal lahir** wajib diisi, maksimal hari ini, dan minimal 99 tahun sebelum hari ini.

Batas kategori usia diatur di `config/church.php`:

```php
'age_groups' => [
    'Anak'   => [0, 12],
    'Remaja' => [13, 17],
    'Pemuda' => [18, 30],
    'Dewasa' => [31, 59],
    'Lansia' => [60, null],
],
```

## Data wilayah

Alamat keluarga memakai data berjenjang dari `database/data/kodepos.csv`:

```text
Provinsi → Kabupaten/Kota → Kecamatan → Kelurahan/Desa → Kode Pos
```

Perintah manual:

```bash
# Impor bila belum ada; duplikat diabaikan
php artisan regions:import

# Hapus seluruh data wilayah lalu impor ulang
php artisan regions:import --fresh

# Impor dari file lain
php artisan regions:import /path/kodepos.csv
```

Detail data wilayah: [docs/REGIONS.md](docs/REGIONS.md).

## Pengujian

```bash
php artisan test
```

Pemeriksaan tambahan:

```bash
./vendor/bin/pint --test
npm run build
```

Cakupan tes mencakup hierarki peran, klasifikasi usia, akses jemaat, akses pengerja, hapus oleh leader, pengelolaan pengguna oleh admin, privasi profil antaranggota keluarga, token verifikasi, halaman verifikasi publik, pengaturan gereja, kunci kode gereja, penomoran KKJ otomatis/reset tahunan, validasi alamat berjenjang, serta impor Excel (service, dry-run, fresh, idempotensi, unggah admin, dan template).

## Catatan produksi

- Arahkan document root web server ke folder `public/`, bukan ke root proyek.
- Set `APP_ENV=production` dan `APP_DEBUG=false`.
- Set `APP_URL` ke domain HTTPS publik agar tautan QR pada kartu dapat dibuka dari luar jaringan gereja.
- Jalankan `php artisan storage:link` agar foto keluarga/jemaat dan logo dapat diakses.
- Jalankan `npm run build`, lalu `php artisan optimize`.
- Pastikan folder `storage/` dan `bootstrap/cache/` dapat ditulis oleh web server.
- Backup database dan folder `storage/app/public` secara berkala.
- Data berisi NIK, tanggal lahir, dan kontak. Terapkan kebijakan privasi, pembatasan akses, dan retensi data sesuai UU PDP.
- Impor `--fresh` bersifat destruktif; jangan dijalankan di produksi tanpa backup.
- Jangan mengunggah `.env`, database SQLite, workbook Excel berisi data jemaat, atau folder upload ke repositori publik.

## Struktur proyek

```text
app/
  Console/Commands/                     # impor workbook dan data wilayah
  Enums/                                # Role, Gender, status
  Http/Controllers/                     # auth, dashboard, KKJ, jemaat, PDF, verifikasi publik, user, pengaturan, wilayah
  Http/Requests/                        # validasi form
  Models/                               # Family, Member, User, ChurchSetting, Region
  Policies/                             # aturan akses per peran
  Services/                             # statistik, QR code, impor wilayah, penomoran KKJ, importer & template Excel
  Support/AgeCategory.php               # kategori usia
config/church.php                       # nilai awal identitas gereja, kartu, kategori usia
database/data/kodepos.csv               # sumber data wilayah
database/migrations/                    # skema KKJ, jemaat, pengguna, pengaturan, wilayah, urutan KKJ
database/seeders/                       # admin dan wilayah
resources/views/                        # Blade: layout, dashboard, form, PDF, pengaturan, error
routes/web.php                          # seluruh rute
docs/                                   # pemetaan data, wilayah, peran, pertanyaan
tests/                                  # unit & feature test
LICENSE                                 # lisensi MIT
CHANGELOG.md                            # riwayat versi aplikasi
```

## Pemecahan masalah

| Masalah | Solusi |
| --- | --- |
| `No application encryption key` | Jalankan `php artisan key:generate`. |
| `Vite manifest not found` | Jalankan `npm run build` (produksi) atau `npm run dev` (pengembangan). |
| `could not find driver` | Aktifkan `pdo_sqlite` atau `pdo_mysql` di `php.ini`. |
| `Data wilayah belum tersedia` | Jalankan `php artisan regions:import`. |
| Kode gereja belum diatur | Login sebagai admin, buka **Pengaturan Gereja**, isi kode 5 karakter. |
| Tidak bisa login / lupa kata sandi | Jalankan `php artisan orcc4church:reset-password email@contoh.com`; tambahkan `--activate` jika akun nonaktif. |
| Logo/foto tidak tampil | Jalankan `php artisan storage:link`. |
| Perubahan tampilan tidak muncul | Jalankan `php artisan view:clear` dan `php artisan optimize:clear`. |
| Izin folder ditolak | Pastikan `storage/` dan `bootstrap/cache/` dapat ditulis oleh web server. |

## Lisensi

ORCC4CHURCH dirilis dengan lisensi **MIT**. Lihat [LICENSE](LICENSE) untuk teks lengkap.

Anda bebas menggunakan, menyalin, mengubah, dan mendistribusikan ulang perangkat lunak ini, dengan tetap menyertakan notice hak cipta dan lisensi.
