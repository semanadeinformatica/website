<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>{{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">

    <link rel="stylesheet" href="{{ public_path(vite('resources/js/app.css', relative: true, hotServer: false)) }}">
    <!-- HACK: lets ignore the fact that the CSS file in the JS file -->
</head>

<body class="bg-2023-bg">
    <table class="m-0 p-0 w-full bg-2023-bg" width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table class="m-0 p-0 w-full" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    {{ $header ?? '' }}

                    <!-- Email Body -->
                    <tr>
                        <td class="w-full" width="100%" cellpadding="0" cellspacing="0"
                            style="border: hidden !important;">
                            <table class="mx-auto max-w-xl border shadow-lg shadow-2023-orange w-full" align="center"
                                cellpadding="0" cellspacing="0" role="presentation">
                                <!-- Body content -->
                                <tr>
                                    <td class="p-8 prose">
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
