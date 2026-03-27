@include('mail._layout', [
    'subject' => 'License rebind request',
    'heading' => 'Manual rebind review requested',
    'slot' => new \Illuminate\Support\HtmlString(
        '<p style="margin:0 0 16px;font-size:16px;line-height:1.6;">A manual rebind review has been requested for <strong>'.e($appName).'</strong> license <strong>'.e($licenseKey).'</strong>.</p>'.
        '<p style="margin:0 0 16px;font-size:16px;line-height:1.6;">Activation ID: '.e($activationId).'<br>Requested machine ID: '.e($requestedMachineId).'<br>Requested installation ID: '.e($requestedInstallationId).'</p>'.
        ($graceUntilText !== null
            ? '<p style="margin:0;font-size:16px;line-height:1.6;">Current grace period ends at '.e($graceUntilText).'.</p>'
            : '<p style="margin:0;font-size:16px;line-height:1.6;">No grace period is currently active.</p>')
    ),
])
