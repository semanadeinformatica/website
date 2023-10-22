@props(['url'])
<tr>
    <td class="p-6" align="center">
        <a href="{{ $url }}" class="inline-block font-bold text-2023-teal-dark">
            @if (trim($slot) === 'SINF')
                <img src="{{ asset('images/sinf logo.png') }}" class="h-12 w-auto max-h-12 object-contain" alt="SINF Logo">
            @else
                {{ $slot }}
            @endif
        </a>
    </td>
</tr>
