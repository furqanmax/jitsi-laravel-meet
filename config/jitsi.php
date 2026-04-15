<?php

return [

    'domain' => env('JITSI_DOMAIN', 'meet.jit.si'),

    'configOverwrite' => [
        'prejoinPageEnabled' => env('JITSI_PREJOIN_ENABLED', false),
        'prejoinConfig' => [
            'enabled' => env('JITSI_PREJOIN_ENABLED', false),
        ],
        'disableDeepLinking' => env('JITSI_DEEP_LINKING', true),
        'startWithAudioMuted' => env('JITSI_START_AUDIO', false),
        'startWithVideoMuted' => env('JITSI_START_VIDEO', false),
    ],

    'interfaceConfigOverwrite' => [
        'SHOW_CHROME_EXTENSION_BANNER' => false,
        'TOOLBAR_BUTTONS' => array_map(
            'trim',
            explode(',', env('JITSI_TOOLBAR_BUTTONS', 'microphone,camera,hangup,chat'))
        ),
    ],

    'timer' => [
        'warning_yellow' => env('JITSI_WARNING_YELLOW', 300),
        'warning_red' => env('JITSI_WARNING_RED', 60),
    ],

];