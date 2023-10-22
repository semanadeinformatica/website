@props(['url', 'color' => 'teal', 'shadow' => 'orange', 'align' => 'center'])

@php
    $color = match ($color) {
        'primary' => 'teal',
        'success' => 'teal-dark',
        'danger' => 'red-dark',
        default => $color,
    };
    $shadow = match ($color) {
        'danger' => 'teal',
        'success', 'primary' => 'orange',
        default => $shadow,
    };
@endphp

<table class="m-8 mx-auto text-center w-full" align="{{ $align }}" width="100%" cellpadding="0" cellspacing="0"
    role="presentation">
    <tr>
        <td align="{{ $align }}">
            <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td align="{{ $align }}">
                        <table border="0" cellpadding="0" cellspacing="0" role="presentation">
                            <tr>
                                <td>
                                    <a href="{{ $url }}"
                                        class="text-white text-md p-2 font-semibold transition-shadow hover:shadow-md active:shadow-none border border-black bg-2023-{{ $color }} shadow-2023-{{ $shadow }}"
                                        target="_blank" rel="noopener">{{ $slot }}</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
