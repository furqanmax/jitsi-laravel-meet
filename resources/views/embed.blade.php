<div id="jitsi" style=" top:0;
    left:0;
    width:100%;
    height:100%;"></div>

<!-- ✅ Loader -->
<div id="jitsi-loader" style="
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:#0f172a;
    display:flex;
    align-items:center;
    justify-content:center;
    flex-direction:column;
    z-index:99999;
    color:white;
    font-family:sans-serif;
">
    <div class="spinner"></div>
    <p style="margin-top:12px;">Joining meeting...</p>
</div>

<!-- ✅ Timer -->
<div id="meeting-timer" style="position:fixed;top:16px;right:16px;z-index:9999;background:rgba(0,0,0,0.75);color:#fff;padding:10px 18px;border-radius:8px;font-size:18px;font-family:monospace;">
    <span id="timer-display">Loading...</span>
</div>

<style>
.spinner {
    width: 50px;
    height: 50px;
    border: 4px solid rgba(255,255,255,0.2);
    border-top-color: #38bdf8;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

<script src="https://{{ config('jitsi.domain') }}/external_api.js"></script>

<script>
(function() {
    const MEETING_CODE = "{{ $meetingCode }}";
    const USER_NAME = "{{ $userName }}";
    const REDIRECT_URL = "{{ config('jitsi.redirect_url') }}";

    const START_TIME = {{ $startTime }};
    const END_TIME   = {{ $endTime }};

    const WARNING_YELLOW = {{ config('jitsi.timer.warning_yellow') }};
    const WARNING_RED    = {{ config('jitsi.timer.warning_red') }};

    const CONFIG_OVERWRITE = @json(config('jitsi.configOverwrite'));
    const INTERFACE_CONFIG = @json(config('jitsi.interfaceConfigOverwrite'));

    const loader = document.getElementById('jitsi-loader');

    const api = new JitsiMeetExternalAPI("{{ config('jitsi.domain') }}", {
        roomName: MEETING_CODE,
        width: "100%",
        height: "100vh",
        parentNode: document.querySelector('#jitsi'),
        userInfo: { displayName: USER_NAME },
        configOverwrite: CONFIG_OVERWRITE,
        interfaceConfigOverwrite: INTERFACE_CONFIG,
    });

    // ✅ Hide loader when meeting is actually joined
    api.addEventListener('videoConferenceJoined', () => {
        loader.style.display = 'none';
    });

    // Optional: fallback (in case event doesn't fire)
    setTimeout(() => {
        loader.style.display = 'none';
    }, 10000);

    const timerDisplay = document.getElementById('timer-display');
    const timerBox = document.getElementById('meeting-timer');

    let meetingEnded = false;

    function pad(n) {
        return String(n).padStart(2, '0');
    }

    function endMeeting(label) {
        if (meetingEnded) return;

        meetingEnded = true;

        try {
            api.executeCommand('hangup');
        } catch (e) {}

        timerDisplay.textContent = label;

        setTimeout(() => {
            if (REDIRECT_URL && window.location.href !== REDIRECT_URL) {
                window.location.href = REDIRECT_URL;
            }
        }, 2000);
    }

    function updateTimer() {
        if (meetingEnded) return;

        const now = Math.floor(Date.now() / 1000);

        const beforeStart = START_TIME - now;
        const remaining   = END_TIME - now;

        if (beforeStart > 0) {
            const m = Math.floor(beforeStart / 60);
            const s = beforeStart % 60;

            timerDisplay.textContent = 'Starts in ' + pad(m) + ':' + pad(s);
            timerBox.style.background = 'rgba(0,0,0,0.75)';
            return;
        }

        if (remaining <= 0) {
            endMeeting('Time is up!');
            return;
        }

        const hours = Math.floor(remaining / 3600);
        const mins  = Math.floor((remaining % 3600) / 60);
        const secs  = remaining % 60;

        timerDisplay.textContent = hours
            ? pad(hours) + ':' + pad(mins) + ':' + pad(secs) + ' left'
            : pad(mins) + ':' + pad(secs) + ' left';

        if (remaining <= WARNING_RED) {
            timerBox.style.background = 'rgba(220,38,38,0.85)';
        } else if (remaining <= WARNING_YELLOW) {
            timerBox.style.background = 'rgba(234,179,8,0.85)';
        } else {
            timerBox.style.background = 'rgba(0,0,0,0.75)';
        }
    }

    updateTimer();
    setInterval(updateTimer, 1000);

    api.addEventListener('readyToClose', () => {
        window.location.href = REDIRECT_URL;
    });

})();
</script>