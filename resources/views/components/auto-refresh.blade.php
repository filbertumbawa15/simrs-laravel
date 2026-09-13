@props([
    'seconds' => 30,
])

{{--
    Auto-refresh badge dengan countdown.
    Pakai di board yang harus selalu up-to-date (bed board, IGD, antrian).

    Fitur:
    - Reload halaman tiap N detik
    - Pause otomatis saat tab tidak aktif (hemat resource server)
    - Tombol pause/resume manual
    - Countdown visual biar user tahu kapan refresh berikutnya

    Usage:
        <x-auto-refresh :seconds="30" />
--}}

<div
    x-data="{
        seconds: {{ (int) $seconds }},
        remaining: {{ (int) $seconds }},
        paused: false,
        timer: null,
        init() {
            this.start();
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) { this.stop(); }
                else if (!this.paused) { this.start(); }
            });
        },
        start() {
            this.stop();
            this.timer = setInterval(() => {
                this.remaining--;
                if (this.remaining <= 0) {
                    window.location.reload();
                }
            }, 1000);
        },
        stop() {
            if (this.timer) { clearInterval(this.timer); this.timer = null; }
        },
        toggle() {
            this.paused = !this.paused;
            if (this.paused) { this.stop(); }
            else { this.remaining = this.seconds; this.start(); }
        },
    }"
    class="inline-flex items-center gap-2 text-xs text-gray-600 bg-white border border-gray-200 rounded-full px-3 py-1 shadow-sm"
>
    <span x-show="!paused" class="flex items-center gap-1.5">
        <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
        </span>
        Refresh dalam <strong x-text="remaining"></strong>s
    </span>
    <span x-show="paused" class="text-gray-400">Auto-refresh dijeda</span>
    <button
        @click="toggle()"
        type="button"
        class="text-gray-400 hover:text-gray-700 text-xs"
        :title="paused ? 'Resume' : 'Pause'"
    >
        <span x-show="!paused">⏸</span>
        <span x-show="paused">▶</span>
    </button>
</div>
