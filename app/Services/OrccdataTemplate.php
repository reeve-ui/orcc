<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrccdataTemplate
{
    private const DATA_ROWS = 2000;

    /**
     * @var array<int, string>
     */
    private const KKJ_HEADERS = [
        'KKJID',
        'KEPALA KELUARGA',
        'MASUK',
        'SUKACITA',
        'PROVINSI',
        'KABUPATEN KOTA',
        'KECAMATAN',
        'KELURAHAN',
        'KODE POS',
        'ALAMAT TINGGAL',
        'PHOTO KELUARGA',
        'QRCODE',
    ];

    /**
     * @var array<int, string>
     */
    private const MEMBER_HEADERS = [
        'KKJID',
        'KTJID',
        'NAMA LENGKAP',
        'ALIAS',
        'STATUS KELUARGA',
        'ANAK KE',
        'JENIS KELAMIN',
        'NOMOR KTP',
        'SELULER',
        'WHATSAPP',
        'EMAIL',
        'TANGGAL LAHIR',
        'STATUS PERKAWINAN',
        'NAMA PASANGAN',
        'PHOTO',
        'PELAYANAN',
        'KOMSEL',
        'KETERANGAN',
        'QRCODE',
    ];

    public function make(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->removeSheetByIndex(0);

        $kkj = $spreadsheet->createSheet();
        $kkj->setTitle('KKJ');
        $kkj->fromArray([self::KKJ_HEADERS], null, 'A1');
        $this->styleDataSheet($kkj, self::KKJ_HEADERS);
        $kkj->getStyle('A2:A'.self::DATA_ROWS)->getNumberFormat()->setFormatCode('@');
        $kkj->getStyle('I2:I'.self::DATA_ROWS)->getNumberFormat()->setFormatCode('@');

        $members = $spreadsheet->createSheet();
        $members->setTitle('JEMAAT');
        $members->fromArray([self::MEMBER_HEADERS], null, 'A1');
        $this->styleDataSheet($members, self::MEMBER_HEADERS);
        $members->getStyle('A2:B'.self::DATA_ROWS)->getNumberFormat()->setFormatCode('@');
        $members->getStyle('H2:H'.self::DATA_ROWS)->getNumberFormat()->setFormatCode('@');
        $members->getStyle('L2:L'.self::DATA_ROWS)->getNumberFormat()->setFormatCode('yyyy-mm-dd');

        $this->addListValidation($members, 'E', 'Kepala Keluarga,Pasangan,Anak,Saudara,Anggota Keluarga');
        $this->addListValidation($members, 'G', 'Pria,Wanita');
        $this->addListValidation($members, 'M', 'Belum Menikah,Telah Menikah,Pernah Menikah');

        $guide = $spreadsheet->createSheet();
        $guide->setTitle('PETUNJUK');
        $this->writeGuide($guide);

        $spreadsheet->setActiveSheetIndexByName('KKJ');

        return $spreadsheet;
    }

    /**
     * @param  array<int, string>  $headers
     */
    private function styleDataSheet(Worksheet $sheet, array $headers): void
    {
        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));

        $sheet->setAutoFilter('A1:'.$lastColumn.'1');
        $sheet->freezePane('A2');
        $sheet->getRowDimension(1)->setRowHeight(28);

        $header = $sheet->getStyle('A1:'.$lastColumn.'1');
        $header->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $header->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FF0F766E');
        $header->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        foreach ($headers as $index => $header) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($index + 1))
                ->setWidth(max(12, min(32, strlen($header) + 2)));
        }
    }

    private function addListValidation(Worksheet $sheet, string $column, string $options): void
    {
        $validation = new DataValidation;
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowDropDown(true);
        $validation->setShowErrorMessage(true);
        $validation->setErrorTitle('Nilai tidak valid');
        $validation->setError('Pilih salah satu nilai yang tersedia.');
        $validation->setFormula1('"'.$options.'"');

        $sheet->setDataValidation($column.'2:'.$column.self::DATA_ROWS, $validation);
    }

    private function writeGuide(Worksheet $sheet): void
    {
        $lines = [
            ['PETUNJUK IMPOR DATA JEMAAT'],
            [''],
            ['1. Jangan mengubah nama sheet "KKJ" dan "JEMAAT".'],
            ['2. Satu baris pada sheet KKJ adalah satu keluarga. KKJID wajib diisi dan harus unik.'],
            ['3. Satu baris pada sheet JEMAAT adalah satu anggota. KKJID dan NAMA LENGKAP wajib diisi.'],
            ['4. KTJID opsional, tetapi jika diisi harus unik dan dipakai sebagai kunci pembaruan anggota.'],
            ['5. Impor tidak menimpa data yang sudah terisi. Gunakan opsi fresh hanya jika ingin menghapus seluruh KKJ dan jemaat lebih dulu.'],
            ['6. Format TANGGAL LAHIR: YYYY-MM-DD atau tanggal Excel.'],
            ['7. STATUS KELUARGA: Kepala Keluarga, Pasangan, Anak, Saudara, Anggota Keluarga.'],
            ['8. JENIS KELAMIN: Pria, Wanita.'],
            ['9. STATUS PERKAWINAN: Belum Menikah, Telah Menikah, Pernah Menikah.'],
            ['10. NIK harus 16 digit; telepon 8-15 digit; email harus valid. Nilai tidak valid dikosongkan dan ditandai.'],
            ['11. PHOTO dan PHOTO KELUARGA berisi nama file/path, bukan berkas gambar. Unggah foto lewat aplikasi.'],
            ['12. QRCODE lama tidak dipakai untuk QR baru. QR pada kartu dibuat dari tautan verifikasi publik.'],
            ['13. Baris tanpa KKJID atau tanpa NAMA LENGKAP akan dilewati dan dihitung.'],
            ['14. Sheet PETUNJUK ini tidak dibaca oleh proses impor.'],
            [''],
            ['CONTOH NILAI'],
            ['KKJID: SEKRT00260001'],
            ['KEPALA KELUARGA: Nama Kepala Keluarga'],
            ['MASUK: 2015'],
            ['PROVINSI: Jawa Barat'],
            ['KABUPATEN KOTA: Kota Depok'],
            ['KECAMATAN: Pancoran Mas'],
            ['KELURAHAN: Depok'],
            ['KODE POS: 16431'],
            ['ALAMAT TINGGAL: Jl. Contoh No. 1'],
            ['KTJID: SEKRT0026000100'],
            ['NAMA LENGKAP: Nama Anggota Keluarga'],
            ['STATUS KELUARGA: Kepala Keluarga'],
            ['JENIS KELAMIN: Pria'],
            ['TANGGAL LAHIR: 1985-04-17'],
            ['STATUS PERKAWINAN: Telah Menikah'],
        ];

        $sheet->fromArray($lines, null, 'A1');
        $sheet->getColumnDimension('A')->setWidth(120);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:A'.count($lines))->getAlignment()->setWrapText(true);
    }
}
