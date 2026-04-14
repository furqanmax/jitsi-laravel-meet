<div id="jitsi"></div>

<div id="meeting-timer" style="position:fixed;top:16px;right:16px;z-index:9999;background:rgba(0,0,0,0.75);color:#fff;padding:10px 18px;border-radius:8px;font-size:18px;font-family:monospace;">
    <span id="timer-display">Loading...</span>
</div>

<script src="https://{{ $jitsiUrl }}/external_api.js"></script>
<script>
(function() {
    const MEETING_CODE = "{{ $meetingCode }}";
    const USER_NAME = "{{ $userName }}";
    const TIME_API_URL = "{{ $timeApiUrl }}";
    const REDIRECT_URL = "{{ $redirectUrl }}";
    const TIMER_SYNC_INTERVAL = {{ $timerSyncInterval }};

    const WARNING_YELLOW = {{ $warningYellow }};
    const WARNING_RED = {{ $warningRed }};

    const api = new JitsiMeetExternalAPI("{{ $jitsiUrl }}", {
        roomName: MEETING_CODE,
        width: "100%",
        height: "100vh",
        parentNode: document.querySelector('#jitsi'),
        userInfo: { displayName: USER_NAME },
        configOverwrite: @json($configOverwrite),
        interfaceConfigOverwrite: @json($interfaceConfig),
    });

    const timerDisplay = document.getElementById('timer-display');
    const timerBox = document.getElementById('meeting-timer');
    let timerInterval;
    let meetingEnded = false;

    function pad(n) { return String(n).padStart(2, '0'); }

    function endMeeting(label) {
        if (meetingEnded) return;
        meetingEnded = true;
        clearInterval(timerInterval);
        api.executeCommand('hangup');
        timerDisplay.textContent = label;
        setTimeout(() => { window.location.href = REDIRECT_URL; }, 2000);
    }

    async function syncWithServer() {
        if (meetingEnded) return;

        try {
            const res = await fetch(TIME_API_URL, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (res.status === 403) {
                endMeeting('Removed from meeting');
                return;
            }

            if (res.status === 404) {
                endMeeting('Meeting not found');
                return;
            }

            const data = await res.json();
            const remaining = data.remaining_seconds;
            const beforeStart = data.before_start_seconds;

            if (beforeStart > 0) {
                const wm = Math.floor(beforeStart / 60), ws = beforeStart % 60;
                timerDisplay.textContent = 'Starts in ' + pad(wm) + ':' + pad(ws);
                timerBox.style.background = 'rgba(0,0,0,0.75)';
                return;
            }

            if (remaining <= 0) {
                endMeeting('Time is up! Redirecting');
                return;
            }

            const hours = Math.floor(remaining / 3600);
            const mins = Math.floor((remaining % 3600) / 60);
            const secs = remaining % 60;

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
        } catch (err) {
            console.warn('Timer sync failed, retrying...', err);
        }
    }

    syncWithServer();
    timerInterval = setInterval(syncWithServer, TIMER_SYNC_INTERVAL);

    api.addEventListener('readyToClose', () => {
        clearInterval(timerInterval);
        window.location.href = REDIRECT_URL;
    });
})();
</script>
