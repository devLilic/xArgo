@include('mail._layout', [
    'subject' => 'License expiry warning',
    'heading' => 'License expiry warning',
    'slot' => new \Illuminate\Support\HtmlString(
        '<p style="margin:0 0 16px;font-size:16px;line-height:1.6;">Hello '.e($customerName).', your license <strong>'.e($licenseKey).'</strong> for <strong>'.e($appName).'</strong> is scheduled to expire at '.e($expiresAtText).'.</p>'.
        '<p style="margin:0;font-size:16px;line-height:1.6;">Review renewal status before service interruption.</p>'
    ),
])
