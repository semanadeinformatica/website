<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>{{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="color-scheme" content="dark">
    <meta name="supported-color-schemes" content="dark">
</head>

<body style="margin: 0; padding: 0; background-color: #0d0e12; color: #e2e8f0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table class="m-0 p-0 w-full" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #0d0e12;">
        <tr>
            <td align="center" style="padding: 20px 10px;">
                <table class="m-0 p-0 w-full" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    {{ $header ?? '' }}

                    <!-- Email Body -->
                    <tr>
                        <td class="w-full" width="100%" cellpadding="0" cellspacing="0">
                            <table class="mx-auto max-w-xl w-full" align="center"
                                cellpadding="0" cellspacing="0" role="presentation"
                                style="max-width: 580px; width: 100%; margin: 0 auto; background-color: #14161f; border-radius: 16px; border: 1px solid rgba(255, 255, 255, 0.08); overflow: hidden;">
                                <!-- Body content -->
                                <tr>
                                    <td style="padding: 32px; color: #e2e8f0; font-size: 15px; line-height: 1.6;">
                                        {{ Illuminate\Mail\Markdown::parse($slot) }}

                                        {{ $subcopy ?? '' }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{ $footer ?? '' }}
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
