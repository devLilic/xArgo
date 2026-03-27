@include('mail._layout', [
    'subject' => 'Device mismatch alert',
    'heading' => 'Suspicious device mismatch detected',
    'slot' => new \Illuminate\Support\HtmlString(
        '<p style="margin:0 0 16px;font-size:16px;line-height:1.6;">A mismatch was detected for <strong>'.e($appName).'</strong> license <strong>'.e($licenseKey).'</strong>.</p>'.
        '<p style="margin:0 0 16px;font-size:16px;line-height:1.6;">Activation ID: '.e($activationId).'<br>Machine ID: '.e($machineId).'<br>Installation ID: '.e($installationId).'<br>Reason code: '.e($reasonCode).'</p>'.
        ($graceUntilText !== null
            ? '<p style="margin:0;font-size:16px;line-height:1.6;">Grace period ends at '.e($graceUntilText).'.</p>'
            : '<p style="margin:0;font-size:16px;line-height:1.6;">No grace period is currently active.</p>')
    ),
])
