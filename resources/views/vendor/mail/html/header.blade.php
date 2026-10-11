@props(['url'])
<tr>
    <td class="p-6" align="center" style="padding: 24px;">
        <a href="{{ $url }}" style="display: inline-block;">
            @if (trim($slot) === 'SINF')
                <img src="{{ asset('images/sinf2026.svg') }}" style="height: 40px; width: auto; max-height: 40px; object-fit: contain;" alt="SINF 2026">
            @else
                {{ $slot }}
            @endif
        </a>
    </td>
</tr>
