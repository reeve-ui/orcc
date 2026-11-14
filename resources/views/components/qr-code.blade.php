@props(['matrix' => [], 'cell' => 5, 'class' => ''])

@if (! empty($matrix))
    <table role="img"
           aria-label="Kode QR KKJ"
           cellspacing="0"
           cellpadding="0"
           {{ $attributes->merge(['class' => $class]) }}
           style="border-collapse:collapse;border-spacing:0;line-height:0;width:auto;">
        @foreach ($matrix as $row)
            <tr>
                @foreach ($row as $dark)
                    <td style="width:{{ $cell }}px;height:{{ $cell }}px;padding:0;margin:0;background:{{ $dark ? '#111827' : '#ffffff' }};"></td>
                @endforeach
            </tr>
        @endforeach
    </table>
@endif
