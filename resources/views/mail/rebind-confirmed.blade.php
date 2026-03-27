@include('mail._layout', [
    'subject' => 'License rebind confirmed',
    'heading' => 'Manual rebind completed',
    'slot' => new \Illuminate\Support\HtmlString(
        '<p style="margin:0 0 16px;font-size:16px;line-height:1.6;">A manual rebind has been completed for <strong>'.e($appName).'</strong> license <strong>'.e($licenseKey).'</strong>.</p>'.
        '<p style="margin:0;font-size:16px;line-height:1.6;">Activation ID: '.e($activationId).'<br>Machine ID: '.e($machineId).'<br>Installation ID: '.e($installationId).'</p>'
    ),
])
