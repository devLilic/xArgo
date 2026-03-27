@include('mail._layout', [
    'subject' => 'You have been invited to the admin panel',
    'heading' => 'Internal team invitation',
    'slot' => new \Illuminate\Support\HtmlString(
        '<p style="margin:0 0 16px;font-size:16px;line-height:1.6;">An internal admin invitation has been prepared for <strong>'.e($inviteeEmail).'</strong>.</p>'.
        '<p style="margin:0 0 16px;font-size:16px;line-height:1.6;">This invitation expires at '.e($expiresAtText).'.</p>'.
        '<p style="margin:24px 0;"><a href="'.e($acceptUrl).'" style="display:inline-block;padding:12px 20px;background:#0f766e;color:#ffffff;text-decoration:none;border-radius:999px;font-weight:700;">Accept invitation</a></p>'
    ),
])
