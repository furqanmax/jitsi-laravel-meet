<?php

namespace Furqanamx\JitsiLaravelMeet\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\Auth;

class JitsiEmbed extends Component
{
    public function __construct(
        public string $meetingCode,
        public ?string $userName = null,
        public ?string $jitsiUrl = null,
        public ?string $redirectUrl = null,
    ) {}

    public function render()
    {
        $jitsiUrl = $this->jitsiUrl ?? config('jitsi.url', 'meet.eshare.ai');
        $redirectUrl = $this->redirectUrl ?? config('jitsi.redirect_url', '/');

        return view('jitsi::embed', [
            'meetingCode' => $this->meetingCode,
            'userName' => $this->userName ?? Auth::user()?->name ?? 'Guest',
            'jitsiUrl' => $jitsiUrl,
            'redirectUrl' => $redirectUrl,
            'timeApiUrl' => route('jitsi.time-remaining', ['code' => $this->meetingCode]),
            'timerSyncInterval' => config('jitsi.timer_sync_interval', 1000),
            'warningYellow' => config('jitsi.warning_thresholds.yellow', 300),
            'warningRed' => config('jitsi.warning_thresholds.red', 120),
            'configOverwrite' => config('jitsi.config_overwrite', []),
            'interfaceConfig' => config('jitsi.interface_config', []),
        ]);
    }
}
