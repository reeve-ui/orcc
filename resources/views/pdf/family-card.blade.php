<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kartu KKJ {{ $family->kkj_id }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 24px 28px 22px 28px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "DejaVu Sans", sans-serif;
            color: #111827;
            font-size: 9.5px;
            line-height: 1.35;
        }

        table {
            border-collapse: collapse;
        }

        .header-table {
            width: 100%;
        }

        .header-table > tbody > tr > td {
            vertical-align: top;
        }

        .qr-cell {
            width: 78px;
            padding-top: 2px;
        }

        .brand-table {
            width: 100%;
        }

        .brand-cell {
            text-align: right;
        }

        .brand-logo {
            width: 40px;
            height: 40px;
            object-fit: contain;
            vertical-align: middle;
        }

        .brand-fallback {
            display: inline-block;
            width: 40px;
            height: 40px;
            line-height: 40px;
            border-radius: 8px;
            background: #1e3a8a;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
        }

        .brand-name {
            display: inline-block;
            margin-left: 8px;
            color: #1e3a8a;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 0.2px;
            vertical-align: middle;
        }

        .doc-title {
            margin-top: 8px;
            color: #111827;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 0.2px;
            text-align: right;
        }

        .info-table {
            width: 100%;
            margin-top: 14px;
        }

        .info-table > tbody > tr > td {
            vertical-align: top;
        }

        .info-left {
            width: 52%;
            padding-right: 18px;
        }

        .info-right {
            width: 48%;
        }

        .info-grid {
            width: 100%;
        }

        .info-grid td {
            padding: 0 0 3px;
            vertical-align: top;
        }

        .info-label {
            width: 108px;
            color: #1e3a8a;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .info-value {
            color: #111827;
            font-size: 9px;
        }

        .rule {
            margin: 12px 0 10px;
            border-top: 1.5px solid #1e3a8a;
        }

        .members {
            width: 100%;
            margin-top: 4px;
        }

        .members thead {
            display: table-header-group;
        }

        .members tr {
            page-break-inside: avoid;
        }

        .members th {
            padding: 6px 5px;
            border: 1px solid #93c5fd;
            background: #dbeafe;
            color: #1e3a8a;
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 0.2px;
            text-align: center;
            text-transform: uppercase;
        }

        .members td {
            padding: 5px;
            border: 1px solid #bfdbfe;
            font-size: 8.2px;
            vertical-align: top;
        }

        .members tbody tr:nth-child(even) td {
            background: #f8fbff;
        }

        .center {
            text-align: center;
        }

        .nowrap {
            white-space: nowrap;
        }

        .muted {
            color: #64748b;
        }

        .member-name {
            color: #111827;
            font-weight: bold;
        }

        .signature {
            width: 260px;
            margin-top: 18px;
            page-break-inside: avoid;
        }

        .signature-date {
            margin-bottom: 22px;
            color: #111827;
            font-size: 9px;
        }

        .signature-name {
            color: #111827;
            font-size: 9px;
            font-weight: bold;
        }

        .signature-title {
            margin-top: 1px;
            color: #334155;
            font-size: 9px;
        }

        .footer-table {
            width: 100%;
            margin-top: 20px;
            page-break-inside: avoid;
        }

        .footer-table td {
            vertical-align: bottom;
        }

        .note {
            color: #475569;
            font-size: 7.5px;
            font-style: italic;
        }

        .version {
            color: #64748b;
            font-size: 7px;
            line-height: 1.45;
            text-align: right;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="qr-cell" rowspan="2">
                @if (! empty($qrMatrix))
                    <x-qr-code :matrix="$qrMatrix" :cell="2" />
                @endif
            </td>
            <td>
                <table class="brand-table">
                    <tr>
                        <td class="brand-cell">
                            @if (! empty($logoDataUri))
                                <img class="brand-logo" src="{{ $logoDataUri }}" alt="Logo {{ $church['name'] }}">
                            @else
                                <span class="brand-fallback">SG</span>
                            @endif
                            <span class="brand-name">{{ \Illuminate\Support\Str::upper($church['name']) }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="brand-cell">
                            <div class="doc-title">KARTU KELUARGA JEMAAT (KKJ)</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td class="info-left">
                <table class="info-grid">
                    <tr>
                        <td class="info-label">Nomor KKJ</td>
                        <td class="info-value">{{ $family->kkj_id }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Kepala Keluarga</td>
                        <td class="info-value">{{ $family->head_name }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Tahun Masuk</td>
                        <td class="info-value">{{ $family->joined_year ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Nomor Sukacita</td>
                        <td class="info-value">{{ $family->joy_number ?: '—' }}</td>
                    </tr>
                </table>
            </td>
            <td class="info-right">
                <table class="info-grid">
                    <tr>
                        <td class="info-label">Alamat Tinggal</td>
                        <td class="info-value">{{ $family->address ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Kel/Kec/Kab/Kota</td>
                        <td class="info-value">
                            {{ $family->location_line }}@if ($family->postal_code) - {{ $family->postal_code }}@endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="rule"></div>

    <table class="members">
        <thead>
            <tr>
                <th style="width: 12%;">Status</th>
                <th style="width: 17%;">Nomor KTJ</th>
                <th style="width: 25%;">Nama Lengkap</th>
                <th style="width: 8%;">Gender</th>
                <th style="width: 12%;">Tgl. Lahir</th>
                <th style="width: 14%;">Nomor HP/WA</th>
                <th style="width: 12%;">Komsel</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($members as $member)
                <tr>
                    <td>{{ $member->family_status }}</td>
                    <td class="nowrap">{{ $member->ktj_id ?: '—' }}</td>
                    <td>
                        <span class="member-name">{{ $member->full_name }}</span>
                        @if ($member->alias && $member->alias !== $member->full_name)
                            <span class="muted"> ({{ $member->alias }})</span>
                        @endif
                    </td>
                    <td class="center">{{ $member->gender?->label() ?? '—' }}</td>
                    <td class="nowrap">{{ $member->birth_date?->translatedFormat('d F Y') ?? '—' }}</td>
                    <td>{{ $member->whatsapp ?: ($member->phone ?: '—') }}</td>
                    <td>{{ $member->cell_group ?: '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="center muted">Belum ada anggota keluarga yang terdata.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature">
        <div class="signature-date">
            {{ \Illuminate\Support\Str::before($church['address'] ?: $church['name'], ',') }}, {{ now()->translatedFormat('d F Y') }}
        </div>
        <div class="signature-name">{{ $church['card']['signature_name'] ?: '(...................................)' }}</div>
        <div class="signature-title">{{ $church['card']['signature_title'] }}</div>
    </div>

    <table class="footer-table">
        <tr>
            <td class="note">*) {{ $church['card']['note'] }}</td>
            <td class="version">
                V.{{ $church['card']['version'] ?? '1.0' }}<br>
                Dicetak pada {{ now()->format('d-m-Y H:i:s') }}
            </td>
        </tr>
    </table>
</body>
</html>
