<?php

return [
    'url' => env('JITSI_URL', 'meet.eshare.ai'),

    'redirect_url' => env('JITSI_REDIRECT_URL', '/'),

    'timer_sync_interval' => env('JITSI_TIMER_SYNC_INTERVAL', 1000),

    'warning_thresholds' => [
        'yellow' => env('JITSI_WARNING_YELLOW_SECONDS', 300),
        'red' => env('JITSI_WARNING_RED_SECONDS', 120),
    ],

    'meeting_model' => env('JITSI_MEETING_MODEL', null),

    'authorize' => env('JITSI_AUTHORIZE', true),

    'check_user_meeting' => env('JITSI_CHECK_USER_MEETING', true),

    'interface_config' => [
        'SHOW_CHROME_EXTENSION_BANNER' => false,
        'TOOLBAR_BUTTONS' => ['microphone', 'camera', 'hangup', 'chat'],
    ],

    'config_overwrite' => [
        'prejoinPageEnabled' => false,
        'prejoinConfig' => ['enabled' => false],
        'disableDeepLinking' => true,
        'startWithAudioMuted' => false,
        'startWithVideoMuted' => false,
    ],
];
