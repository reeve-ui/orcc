<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Identitas gereja
    |--------------------------------------------------------------------------
    | Semua nilai dapat diubah lewat .env tanpa menyentuh kode.
    */
    'name' => env('CHURCH_NAME', 'Sekretariat Gereja'),
    'tagline' => env('CHURCH_TAGLINE', 'Data Jemaat'),
    'address' => env('CHURCH_ADDRESS', 'Alamat gereja'),
    'phone' => env('CHURCH_PHONE', ''),
    'email' => env('CHURCH_EMAIL', 'sekretariat@example.com'),
    'code' => env('CHURCH_CODE'),
    'logo' => env('CHURCH_LOGO'),

    /*
    |--------------------------------------------------------------------------
    | Kartu Keluarga Jemaat (KKJ)
    |--------------------------------------------------------------------------
    */
    'card' => [
        'paper' => env('CHURCH_CARD_PAPER', 'a4'),
        'note' => env(
            'CHURCH_CARD_NOTE',
            'Kartu ini diterbitkan oleh Sekretariat Gereja. Mohon simpan dengan baik.'
        ),
        'signature_name' => env('CHURCH_CARD_SIGNATURE_NAME', ''),
        'signature_title' => env('CHURCH_CARD_SIGNATURE', 'Sekretariat Gereja'),
        'version' => env('CHURCH_CARD_VERSION', '1.0'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Kategori usia dashboard
    |--------------------------------------------------------------------------
    | Batas bawah dan batas atas (inklusif). null = tanpa batas atas.
    */
    'age_groups' => [
        'Anak' => [0, 12],
        'Remaja' => [13, 17],
        'Pemuda' => [18, 30],
        'Dewasa' => [31, 59],
        'Lansia' => [60, null],
    ],

];
