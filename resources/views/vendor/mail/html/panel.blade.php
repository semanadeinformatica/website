<table class="border" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td class="bg-2023-teal-dark text-white">
            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td class="p-0 prose prose-white">
                        {{ Illuminate\Mail\Markdown::parse($slot) }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
