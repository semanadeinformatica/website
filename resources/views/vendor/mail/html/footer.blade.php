<tr>
    <td>
        <table class="max-w-xl text-center" align="center" role="presentation" style="max-width: 580px; margin: 0 auto;">
            <tr>
                <td style="padding: 24px 16px; font-size: 12px; color: #71717a; text-align: center;" align="center">
                    {{ Illuminate\Mail\Markdown::parse($slot) }}
                </td>
            </tr>
        </table>
    </td>
</tr>
