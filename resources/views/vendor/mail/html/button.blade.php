@props(['url', 'color' => 'primary', 'align' => 'center'])

<table class="mx-auto text-center w-full" align="{{ $align }}" width="100%" cellpadding="0" cellspacing="0"
    role="presentation" style="margin: 24px 0;">
    <tr>
        <td align="{{ $align }}">
            <table border="0" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td align="{{ $align }}">
                        <a href="{{ $url }}"
                            style="display: inline-block; padding: 10px 24px; border-radius: 9999px; background: linear-gradient(135deg, #9a0036 0%, #6a00ff 100%); color: #ffffff; font-weight: 600; font-size: 14px; text-decoration: none; box-shadow: 0 4px 14px rgba(154, 0, 54, 0.35);"
                            target="_blank" rel="noopener">{{ $slot }}</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
