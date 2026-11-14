# Peran, Hak Akses, dan Keamanan

Aplikasi memakai empat peran dengan hierarki angka: semakin kecil nilainya, semakin tinggi aksesnya.

| Nilai | Peran | Ringkasan |
| ---: | --- | --- |
| 1 | `admin` | Akses penuh, termasuk pengelolaan akun |
| 2 | `leader` | Pendeta, ketua majelis, ketua yayasan, atau kepala sekretariat. Lihat & kelola seluruh data, termasuk hapus |
| 3 | `pengerja` | Lihat & kelola seluruh data, tanpa hapus |
| 4 | `jemaat` | Hanya keluarga dan profil sendiri |

Sumber aturan: `app/Enums/Role.php`, `app/Policies/*.php`.

## Matriks kemampuan

| Tindakan | Admin | Leader | Pengerja | Jemaat |
| --- | :---: | :---: | :---: | :---: |
| Masuk aplikasi | ✓ | ✓ | ✓ | ✓ |
| Dashboard staf (statistik seluruh gereja) | ✓ | ✓ | ✓ | — |
| Dashboard jemaat (keluarga sendiri) | ✓ | ✓ | ✓ | ✓ |
| Daftar & detail seluruh KKJ | ✓ | ✓ | ✓ | — |
| Detail KKJ sendiri | ✓ | ✓ | ✓ | ✓ |
| Tambah KKJ | ✓ | ✓ | ✓ | — |
| Ubah KKJ | ✓ | ✓ | ✓ | — |
| Hapus KKJ | ✓ | ✓ | — | — |
| Daftar & detail seluruh jemaat | ✓ | ✓ | ✓ | — |
| Lihat profil sendiri | ✓ | ✓ | ✓ | ✓ |
| Tambah jemaat | ✓ | ✓ | ✓ | — |
| Ubah jemaat | ✓ | ✓ | ✓ | — |
| Hapus jemaat | ✓ | ✓ | — | — |
| Unduh/pratinjau kartu KKJ sendiri | ✓ | ✓ | ✓ | ✓ |
| Unduh/pratinjau kartu KKJ keluarga lain | ✓ | ✓ | ✓ | — |
| Kelola akun pengguna | ✓ | — | — | — |
| Kelola pengaturan gereja (identitas, logo, kode KKJ, impor Excel) | ✓ | — | — | — |
| Reset seluruh data KKJ & jemaat | ✓ | — | — | — |

## Aturan per policy

### `FamilyPolicy`

| Method | Aturan |
| --- | --- |
| `viewAny` | Hanya staf (admin, leader, pengerja) |
| `view` | Staf, atau jemaat yang `member.family_id` sama dengan KKJ |
| `create` | Staf |
| `update` | Staf |
| `delete` | Admin dan leader |

### `MemberPolicy`

| Method | Aturan |
| --- | --- |
| `viewAny` | Hanya staf |
| `view` | Staf, atau jemaat yang memiliki record anggota tersebut |
| `create` | Staf |
| `update` | Staf |
| `delete` | Admin dan leader |

Konsekuensi: pada halaman keluarga sendiri, jemaat hanya melihat tautan profil untuk dirinya sendiri. Anggota lain tetap tampil sebagai nama di tabel, tanpa tautan yang akan menghasilkan 403.

### `UserPolicy`

| Method | Aturan |
| --- | --- |
| `viewAny`, `create`, `update` | Hanya admin |
| `delete` | Admin, dan bukan akun sendiri |

## Pengelolaan akun

- Akun pengguna hanya dapat dibuat/diubah/dihapus oleh admin.
- Setiap akun dapat ditautkan ke satu record anggota (`member_id`) agar jemaat dapat melihat keluarganya.
- Admin tidak dapat menurunkan role dirinya sendiri atau menonaktifkan akunnya sendiri.
- Admin terakhir tidak dapat dihapus atau diturunkan menjadi non-admin.
- Akun nonaktif (`is_active = false`) tidak dapat login.
- Kata sandi minimal 8 karakter dan harus dikonfirmasi.
- Email dinormalisasi menjadi huruf kecil dan unik.

## Pengaturan gereja

- Hanya admin yang dapat membuka `/pengaturan`.
- Admin dapat mengubah nama, tagline, alamat, telepon, email, logo, catatan kartu, serta nama dan jabatan penanda tangan.
- Admin dapat mengunggah file Excel dan mengunduh template impor dari halaman yang sama. `OrccdataImportRequest` menolak pengguna non-admin dan meminta konfirmasi tambahan untuk impor `fresh`.
- Admin dapat mereset seluruh data KKJ dan jemaat dari zona berbahaya di halaman pengaturan. `ResetDataRequest` menolak pengguna non-admin, mewajibkan teks konfirmasi `RESET DATA`, dan meminta centang pernyataan. Reset menghapus KKJ, jemaat, foto terkait, dan urutan nomor KKJ; akun pengguna, pengaturan gereja, dan data wilayah tetap dipertahankan.
- Kode gereja 5 karakter dipakai sebagai awalan nomor KKJ baru. Setelah dipakai pada nomor KKJ, kode tidak dapat diubah.
- Logo disimpan di disk `public` dan dipakai pada sidebar, halaman verifikasi, serta kartu PDF.

## Keamanan sesi

- Login memakai session auth Laravel; tidak ada token API publik.
- Setelah login, session di-regenerate.
- Logout meng-invalidate session dan mengganti CSRF token.

## Verifikasi kartu publik

QR pada kartu KKJ mengarah ke `/verifikasi/{token}`. Token acak 32 karakter disimpan di `families.verification_token` dan bersifat rahasia seperti kata sandi: siapa pun yang memiliki tautan dapat membuka halaman ringkasan.

Halaman ini:

- tidak memerlukan login,
- dibatasi 120 permintaan per menit per alamat IP,
- hanya menampilkan nomor KKJ, kepala keluarga, jumlah anggota, wilayah, dan tahun masuk,
- tidak menampilkan NIK, tanggal lahir, alamat lengkap, telepon, email, atau daftar nama anggota lain,
- memakai `noindex, nofollow` agar tidak diindeks mesin pencari.

Token bersifat tetap per keluarga. Jika tautan disalahgunakan, token dapat diganti manual oleh pengelola basis data; kartu lama harus dicetak ulang.
- Percobaan login dibatasi 5 kali per kombinasi email + IP, dengan jeda 60 detik.
- Semua form POST/PUT/DELETE memakai CSRF token.
- Route data dilindungi middleware `auth` dan policy di controller.
- Halaman error kustom tidak membocorkan stack trace saat `APP_DEBUG=false`.

## Catatan privasi

Data berisi NIK, tanggal lahir, alamat, nomor telepon, dan email. Rekomendasi:

1. Terapkan prinsip akses minimum; jangan memberi role staf kepada jemaat biasa.
2. Aktifkan HTTPS di produksi.
3. Batasi akses database dan backup.
4. Tetapkan masa retensi dan prosedur penghapusan data.
5. Minta persetujuan jemaat untuk penyimpanan data pribadi sesuai UU PDP.
6. Tinjau apakah anggota keluarga boleh saling melihat profil lengkap. Saat ini mereka hanya melihat nama pada halaman keluarga, bukan detail profil.

## Peran yang masih perlu dikonfirmasi

- **Leader**: saat ini melihat seluruh data gereja. Jika leader seharusnya hanya melihat komsel/wilayahnya, perlu relasi tambahan antara user dan komsel.
- **Pengerja**: saat ini dapat menambah dan mengubah semua data. Jika pengerja seharusnya hanya input tanpa melihat NIK/kontak, perlu pembatasan kolom.
- **Jemaat**: saat ini hanya dapat membuka profilnya sendiri, tetapi dapat melihat nama anggota keluarganya pada halaman KKJ.

Pertanyaan terkait ada di [QUESTIONS.md](QUESTIONS.md).
