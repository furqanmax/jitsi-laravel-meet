<div id="jitsi"></div>

<div id="meeting-timer" style="position:fixed;top:16px;right:16px;z-index:9999;background:rgba(0,0,0,0.75);color:#fff;padding:10px 18px;border-radius:8px;font-size:18px;font-family:monospace;">
    <span id="timer-display">Loading...</span>
</div>

<script src="https://{{ $jitsiUrl }}/external_api.js"></script>
<script>
(function() {
    const MEETING_CODE = "{{ $meetingCode }}";
    const USER_NAME = "{{ $userName }}";
    const REDIRECT_URL = "{{ $redirectUrl }}";

    const START_TIME = {{ $startTime }};
    const END_TIME   = {{ $endTime }};

    const WARNING_YELLOW = {{ $warningYellow }};
    const WARNING_RED = {{ $warningRed }};

    const api = new JitsiMeetExternalAPI("{{ $jitsiUrl }}", {
        roomName: MEETING_CODE,
        width: "100%",
        height: "100vh",
        parentNode: document.querySelector('#jitsi'),
        userInfo: { displayName: USER_NAME },
         configOverwrite: {
                    prejoinPageEnabled : false,
                    prejoinConfig      : { enabled: false },
                    disableDeepLinking : true,
                    startWithAudioMuted: false,
                    startWithVideoMuted: false,
                },
                interfaceConfigOverwrite: {
                    SHOW_CHROME_EXTENSION_BANNER: false,
                    TOOLBAR_BUTTONS: ['microphone', 'camera', 'hangup', 'chat'],
                },
    });

    const timerDisplay = document.getElementById('timer-display');
    const timerBox = document.getElementById('meeting-timer');

    let meetingEnded = false;

    function pad(n) {
        return String(n).padStart(2, '0');
    }

    function endMeeting(label) {
        if (meetingEnded) return;

        meetingEnded = true;
        api.executeCommand('hangup');
        timerDisplay.textContent = label;

        setTimeout(() => {
            window.location.href = REDIRECT_URL;
        }, 2000);
    }

    function updateTimer() {
        if (meetingEnded) return;

        const now = Math.floor(Date.now() / 1000);

        const beforeStart = START_TIME - now;
        const remaining   = END_TIME - now;

        // ── Before meeting ──
        if (beforeStart > 0) {
            const m = Math.floor(beforeStart / 60);
            const s = beforeStart % 60;

            timerDisplay.textContent = 'Starts in ' + pad(m) + ':' + pad(s);
            timerBox.style.background = 'rgba(0,0,0,0.75)';
            return;
        }

        // ── Meeting ended ──
        if (remaining <= 0) {
            endMeeting('Time is up!');
            return;
        }

        // ── Countdown ──
        const hours = Math.floor(remaining / 3600);
        const mins  = Math.floor((remaining % 3600) / 60);
        const secs  = remaining % 60;

        timerDisplay.textContent = hours
            ? pad(hours) + ':' + pad(mins) + ':' + pad(secs) + ' left'
            : pad(mins) + ':' + pad(secs) + ' left';

        // ── Warning colors ──
        if (remaining <= WARNING_RED) {
            timerBox.style.background = 'rgba(220,38,38,0.85)';
        } else if (remaining <= WARNING_YELLOW) {
            timerBox.style.background = 'rgba(234,179,8,0.85)';
        } else {
            timerBox.style.background = 'rgba(0,0,0,0.75)';
        }
    }

    // Run every second
    updateTimer();
    setInterval(updateTimer, 1000);

    // ── Manual hangup ──
    api.addEventListener('readyToClose', () => {
        window.location.href = REDIRECT_URL;
    });

})();
</script>
