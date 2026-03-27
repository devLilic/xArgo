@include('mail._layout', [
    'subject' => 'Trial ending warning',
    'heading' => 'Trial ending warning',
    'slot' => new \Illuminate\Support\HtmlString(
        '<p style="margin:0 0 16px;font-size:16px;line-height:1.6;">Hello '.e($customerName).', the trial for <strong>'.e($appName).'</strong> under license <strong>'.e($licenseKey).'</strong> ends at '.e($trialEndsAtText).'.</p>'.
        '<p style="margin:0;font-size:16px;line-height:1.6;">If continued access is needed, convert the trial before it ends.</p>'
    ),
])
