@extends('errors.layout')

@section('title', 'Terlalu banyak permintaan')
@section('code', '429')
@section('heading', 'Terlalu banyak percobaan')
@section('message', 'Sistem membatasi jumlah permintaan untuk menjaga keamanan. Tunggu beberapa saat lalu coba lagi.')
