<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $subject }}</title>
</head>
<body style="margin:0;padding:24px;background:#f5f7fb;color:#132238;font-family:Arial,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center">
                <table role="presentation" width="640" cellspacing="0" cellpadding="0" style="max-width:640px;width:100%;background:#ffffff;border:1px solid #d9e2ec;border-radius:16px;">
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 8px;font-size:12px;letter-spacing:0.18em;text-transform:uppercase;color:#0f766e;font-weight:700;">
                                {{ config('app.name') }}
                            </p>
                            <h1 style="margin:0 0 20px;font-size:28px;line-height:1.2;color:#132238;">{{ $heading }}</h1>
                            {{ $slot }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
