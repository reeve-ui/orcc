# Data Wilayah dan Alamat Berjenjang

Alamat keluarga memakai pilihan berjenjang:

```text
Provinsi → Kabupaten/Kota → Kecamatan → Kelurahan/Desa → Kode Pos
```

Semua pilihan berasal dari tabel `regions`, yang diimpor dari `database/data/kodepos.csv`.

## Sumber data

File: `database/data/kodepos.csv`

| Kolom CSV | Kolom database | Contoh |
| --- | --- | --- |
| `province` | `province` | `Jawa Barat` |
| `city` | `city` | `Depok` |
| `district` | `district` | `Tapos` |
| `subdistrict` | `village` | `Sukamaju Baru` |
| `postal_code` | `postal_code` | `16455` |

Jumlah data:

- 34 provinsi
- 475 kabupaten/kota
- 6.994 kecamatan
- 80.852 kelurahan/desa unik
- 81.266 baris CSV; 414 baris duplikat persis diabaikan saat impor

Catatan: nama kabupaten/kota mengikuti CSV, umumnya tanpa awalan `Kota` atau `Kabupaten` (contoh: `Depok`, bukan `Kota Depok`). Data lama yang masih memakai awalan akan dicocokkan otomatis saat form dibuka, lalu disimpan dengan nama dari CSV.

## Impor

Saat `php artisan migrate --seed`, `RegionSeeder` otomatis mengimpor CSV bila tabel `regions` masih kosong.

Perintah manual:

```bash
# Impor bila belum ada; duplikat diabaikan
php artisan regions:import

# Hapus seluruh data wilayah lalu impor ulang
php artisan regions:import --fresh

# Impor dari file lain
php artisan regions:import /path/kodepos.csv
```

Impor berjalan dalam satu transaksi dan memakai batch 1.000 baris. Baris dengan kolom kosong, kode pos bukan 5 digit, atau nama wilayah lebih dari 100 karakter dilewati.

## Skema

Tabel `regions`:

| Kolom | Tipe | Keterangan |
| --- | --- | --- |
| `id` | bigint | Primary key |
| `province` | varchar(100) | Provinsi |
| `city` | varchar(100) | Kabupaten/kota |
| `district` | varchar(100) | Kecamatan |
| `village` | varchar(100) | Kelurahan/desa |
| `postal_code` | varchar(5) | Kode pos |

Indeks disediakan untuk setiap tingkat pencarian, dan kombinasi lima kolom bersifat unik.

## API pilihan

Endpoint memerlukan login dan dibatasi 120 permintaan/menit:

| Endpoint | Parameter | Hasil |
| --- | --- | --- |
| `GET /wilayah/provinsi` | — | Daftar provinsi |
| `GET /wilayah/kabupaten` | `province` | Daftar kabupaten/kota |
| `GET /wilayah/kecamatan` | `province`, `city` | Daftar kecamatan |
| `GET /wilayah/kelurahan` | `province`, `city`, `district` | Daftar kelurahan/desa |
| `GET /wilayah/kodepos` | `province`, `city`, `district`, `village` | Daftar kode pos |

Respons:

```json
{
  "items": ["Jawa Barat", "Jawa Tengah"]
}
```

Komponen Alpine `addressPicker` di `resources/js/app.js` memuat pilihan secara berurutan, mengosongkan pilihan turunan saat pilihan induk berubah, dan memilih kode pos otomatis bila hanya ada satu.

## Validasi

`FamilyRequest` mewajibkan:

- provinsi,
- kabupaten/kota,
- kecamatan,
- kelurahan/desa,
- kode pos 5 digit,
- alamat lengkap.

Server memeriksa bahwa kombinasi kelima nilai benar-benar ada di tabel `regions`. Kombinasi yang tidak ditemukan ditolak dengan pesan agar alamat dipilih ulang.

Karena alamat lama hasil impor Excel berupa teks bebas, beberapa alamat mungkin perlu dipilih ulang saat keluarga diubah. Ini disengaja agar data wilayah menjadi konsisten.

## Memperbarui data wilayah

1. Ganti `database/data/kodepos.csv` dengan file baru (format kolom harus sama).
2. Jalankan `php artisan regions:import --fresh`.
3. Periksa kembali keluarga yang alamatnya memakai nama wilayah lama.

`--fresh` hanya menghapus tabel `regions`; data keluarga dan jemaat tidak dihapus. Namun, alamat keluarga yang sudah tersimpan sebagai teks tidak otomatis berubah.
