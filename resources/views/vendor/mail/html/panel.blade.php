<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 20px 0;">
    <tr>
        <td style="background-color: #1a1c26; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.08); padding: 16px; color: #e2e8f0;">
            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td style="padding: 0; font-size: 14px; line-height: 1.5;">
                        {{ Illuminate\Mail\Markdown::parse($slot) }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
