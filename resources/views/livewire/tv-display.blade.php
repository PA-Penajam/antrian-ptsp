<div class="h-screen bg-slate-50 text-slate-900 overflow-hidden p-5"
     x-data="{
         connected: navigator.onLine,
         debugEnabled: window.location.search.indexOf('debug=1') !== -1 || window.location.hash.indexOf('debug') !== -1,
         debugLines: [],
         audioUnlocked: false,
         pendingTtsText: '',
         videos: @js($videos),
         currentIndex: 0,
         videoElement: null,
         videoPausedForTts: false,
         wasVideoPlayingBeforeTts: false,
         isSamsungTv: /Tizen|SMART-TV|SamsungBrowser|Maple/i.test(navigator.userAgent),
         isLgTv: /Web0S|WebOS|webOS|LG Browser|NetCast/i.test(navigator.userAgent),
         videoVolume: @js((float) config('tv.video_volume')),
         videoVolumeDuringTts: @js((float) config('tv.video_volume_during_tts')),
         ttsVolume: @js((float) config('tv.tts_volume')),
         silentAudio: 'data:audio/wav;base64,UklGRigAAABXQVZFZm10IBIAAAABAAEARKwAAIhYAQACABAAAABkYXRhAgAAAAEA',
         get hasVideos() { return this.videos.length > 0 },
         get currentVideo() { return this.hasVideos ? this.videos[this.currentIndex] : '' },
         get shouldIsolateTtsAudio() { return false },
         debugLog(message, context) {
             let detail = '';
             if (context) {
                 try {
                     detail = ' ' + JSON.stringify(context);
                 } catch (error) {
                     detail = ' [context unavailable]';
                 }
             }

             const line = new Date().toLocaleTimeString('id-ID', { hour12: false }) + ' ' + message + detail;
             if (window.console && window.console.log) {
                 window.console.log('[TV]', message, context || '');
             }

             if (!this.debugEnabled) return;

             this.debugLines.unshift(line);
             this.debugLines = this.debugLines.slice(0, 20);
         },
         debugError(error) {
             if (!error) return null;

             return {
                 name: error.name || '',
                 message: error.message || String(error),
                 code: error.code || '',
             };
         },
         shortUrl(value) {
             if (!value) return '';

             return value.length > 90 ? value.slice(0, 90) + '...' : value;
         },
         mediaState(element) {
             if (!element) return null;

             return {
                 paused: element.paused,
                 ended: element.ended,
                 muted: element.muted,
                 volume: element.volume,
                 readyState: element.readyState,
                 networkState: element.networkState,
                 currentTime: Number(element.currentTime || 0).toFixed(2),
                 src: this.shortUrl(element.currentSrc || element.src || ''),
                 error: element.error ? { code: element.error.code, message: element.error.message || '' } : null,
             };
         },
         audio() {
             return this.$refs.ttsAudio;
         },
         video() {
             return this.videoElement || this.$refs.videoPlayer;
         },
         clearAudioSource(audio) {
             if (!audio) return;

             audio.removeAttribute('src');
             if (!this.isLgTv) {
                 audio.load();
             }
         },
         registerVideo(element) {
             this.videoElement = element;
             this.debugLog('video registered', this.mediaState(element));
             this.playCurrentVideo();
         },
         unlockAudio() {
             if (this.audioUnlocked) return;
             const audio = this.audio();
             if (!audio) {
                 this.debugLog('unlock skipped: audio element missing');
                 this.audioUnlocked = true;
                 return;
             }

             const vid = this.video();
             const shouldResumeVideo = this.shouldIsolateTtsAudio && vid && !vid.paused && !vid.ended;
             this.debugLog('unlock start', {
                 isLgTv: this.isLgTv,
                 isSamsungTv: this.isSamsungTv,
                 shouldIsolateTtsAudio: this.shouldIsolateTtsAudio,
                 shouldResumeVideo: shouldResumeVideo,
                 audio: this.mediaState(audio),
                 video: this.mediaState(vid),
             });

             if (this.shouldIsolateTtsAudio && vid && !vid.paused) {
                 this.debugLog('unlock pauses video for isolated audio', this.mediaState(vid));
                 vid.pause();
             }

             const finishUnlock = () => {
                 audio.pause();
                 this.clearAudioSource(audio);
                 audio.volume = this.ttsVolume;
                 this.audioUnlocked = true;
                 this.debugLog('unlock finished', {
                     audio: this.mediaState(audio),
                     video: this.mediaState(vid),
                 });

                 if (!this.shouldIsolateTtsAudio || shouldResumeVideo || this.hasVideos) {
                     this.playCurrentVideo();
                 }

                 this.playPendingTts();
             };

             audio.pause();
             audio.src = this.silentAudio;
             audio.volume = this.isLgTv ? 0 : this.ttsVolume;
             if (!this.isLgTv) {
                 audio.load();
             }

             const unlockPromise = audio.play();
             if (unlockPromise && typeof unlockPromise.then === 'function') {
                 unlockPromise
                     .then(() => {
                         this.debugLog('silent unlock play resolved', this.mediaState(audio));
                         finishUnlock();
                     })
                     .catch((error) => {
                         console.error('Audio unlock failed:', error);
                         this.debugLog('silent unlock play failed', this.debugError(error));
                         finishUnlock();
                     });

                 return;
             }

             finishUnlock();
         },
         playCurrentVideo() {
             const vid = this.video();
             if (!vid || !this.hasVideos) {
                 this.debugLog('video play skipped', { hasVideo: !!vid, hasVideos: this.hasVideos });
                 return;
             }

             if (vid.getAttribute('src') !== this.currentVideo) {
                 vid.src = this.currentVideo;
                 vid.load();
                 this.debugLog('video source changed', this.mediaState(vid));
             }

             vid.muted = !this.audioUnlocked;
             vid.volume = this.videoVolume;
             const playPromise = vid.play();
             if (playPromise && typeof playPromise.then === 'function') {
                 playPromise
                     .then(() => this.debugLog('video play resolved', this.mediaState(vid)))
                     .catch((error) => this.debugLog('video play failed', this.debugError(error)));
             }
         },
         playNext() {
             if (!this.hasVideos) return;
             this.currentIndex = (this.currentIndex + 1) % this.videos.length;
             this.$nextTick(() => this.playCurrentVideo());
         },
         restoreVideoVolume() {
             const vid = this.video();
             if (!vid) return;
             this.debugLog('restore video volume', this.mediaState(vid));

             if (this.shouldIsolateTtsAudio) {
                 if (this.videoPausedForTts && this.wasVideoPlayingBeforeTts) {
                     this.playCurrentVideo();
                 }

                 this.videoPausedForTts = false;
                 this.wasVideoPlayingBeforeTts = false;

                 return;
             }

             vid.volume = this.videoVolume;
         },
         lowerVideoVolume() {
             const vid = this.video();
             if (!vid) return;
             this.debugLog('lower video volume', this.mediaState(vid));

             if (this.shouldIsolateTtsAudio) {
                 if (!this.videoPausedForTts) {
                     this.wasVideoPlayingBeforeTts = !vid.paused && !vid.ended;

                     if (!vid.paused) {
                         vid.pause();
                     }

                     this.videoPausedForTts = true;
                 }

                 return;
             }

             vid.volume = this.videoVolumeDuringTts;
         },
         speakWithBrowserTts(text) {
             this.debugLog('browser tts start', {
                 hasSpeechSynthesis: 'speechSynthesis' in window,
                 text: text,
             });
             this.lowerVideoVolume();

             if (!('speechSynthesis' in window)) {
                 this.debugLog('browser tts unavailable');
                 this.restoreVideoVolume();
                 return;
             }

             window.speechSynthesis.cancel();

             const utterance = new SpeechSynthesisUtterance(text.replace(/,/g, ' '));
             utterance.lang = 'id-ID';
             utterance.rate = 0.95;
             utterance.pitch = 1;
             utterance.volume = this.ttsVolume;
             utterance.onend = () => {
                 this.debugLog('browser tts ended');
                 this.restoreVideoVolume();
             };
             utterance.onerror = (event) => {
                 this.debugLog('browser tts error', {
                     error: event.error || '',
                 });
                 this.restoreVideoVolume();
             };

             window.speechSynthesis.speak(utterance);
         },
         playPendingTts() {
             if (!this.audioUnlocked || !this.pendingTtsText) return;

             const text = this.pendingTtsText;
             this.pendingTtsText = '';
             this.debugLog('tts replay after audio unlock', { text: text });
             this.playTts(text);
         },
         playTts(text) {
             if (!this.audioUnlocked) {
                 this.pendingTtsText = text;
                 this.debugLog('tts deferred until audio unlock', { text: text });

                 return;
             }

             this.debugLog('tts request start', { text: text });
             fetch('/tv-display/tts/announcement?text=' + encodeURIComponent(text))
                 .then(res => {
                     this.debugLog('tts request response', { status: res.status, ok: res.ok });
                     return res.json();
                 })
                 .then(data => {
                     this.debugLog('tts response json', {
                         provider: data.provider || '',
                         audioUrl: this.shortUrl(data.audio_url || ''),
                     });

                     if (!data.audio_url) {
                         this.speakWithBrowserTts(text);
                         return;
                     }

                     const audio = this.audio();
                     if (!audio) {
                         this.debugLog('tts audio element missing');
                         this.speakWithBrowserTts(text);
                         return;
                     }

                     audio.pause();
                     audio.src = data.audio_url;
                     audio.volume = this.ttsVolume;
                     if (!this.isLgTv) {
                         audio.load();
                     }
                     this.debugLog('tts audio prepared', this.mediaState(audio));
                     audio.onended = () => {
                         this.debugLog('tts audio ended', this.mediaState(audio));
                         this.restoreVideoVolume();
                     };
                     audio.onerror = () => {
                         this.debugLog('tts audio error', this.mediaState(audio));
                         this.clearAudioSource(audio);
                         this.speakWithBrowserTts(text);
                     };

                     this.lowerVideoVolume();

                     const playPromise = audio.play();
                     if (playPromise && typeof playPromise.catch === 'function') {
                         playPromise
                             .then(() => this.debugLog('tts audio play resolved', this.mediaState(audio)))
                             .catch((error) => {
                                 console.error('TTS Playback failed:', error);
                                 this.debugLog('tts audio play failed', this.debugError(error));
                                 this.clearAudioSource(audio);
                                 this.speakWithBrowserTts(text);
                             });
                     }
                 })
                 .catch((error) => {
                     console.error('TTS Fetch failed:', error);
                     this.debugLog('tts fetch failed', this.debugError(error));
                     this.speakWithBrowserTts(text);
                 });
         }
     }"
     x-init="debugLog('init', { userAgent: navigator.userAgent, isLgTv: isLgTv, isSamsungTv: isSamsungTv, videos: videos.length, audioUnlocked: audioUnlocked })"
     x-on:online.window="connected = true"
     x-on:offline.window="connected = false"
     x-on:click.window="unlockAudio()"
     x-on:keydown.window="unlockAudio()"
     x-on:play-tts.window="playTts($event.detail.text)">

    <audio x-ref="ttsAudio"
           preload="auto"
           x-on:playing="debugLog('tts audio event playing', mediaState($el))"
           x-on:ended="debugLog('tts audio event ended', mediaState($el))"
           x-on:error="debugLog('tts audio event error', mediaState($el))"></audio>

    <div x-show="debugEnabled"
         x-cloak
         class="fixed bottom-3 left-3 z-[200] w-[min(46rem,calc(100vw-1.5rem))] max-h-[45vh] overflow-hidden rounded-lg border border-amber-300/60 bg-black/85 p-3 font-mono text-[11px] leading-relaxed text-amber-100 shadow-2xl">
        <div class="mb-2 flex items-center justify-between gap-3 border-b border-white/15 pb-2 text-xs font-bold text-white">
            <span>TV Debug</span>
            <span x-text="debugLines.length + ' logs'"></span>
        </div>
        <div class="max-h-[36vh] overflow-y-auto">
            <template x-for="line in debugLines" :key="line">
                <div class="border-b border-white/10 py-1" x-text="line"></div>
            </template>
        </div>
    </div>

    {{-- Audio Unlock Overlay --}}
    <div x-show="!audioUnlocked"
         class="fixed inset-0 z-[100] bg-slate-900/80 backdrop-blur-sm flex flex-col items-center justify-center text-white cursor-pointer"
         x-transition.opacity.duration.500ms>
        <div class="bg-blue-600 rounded-full p-6 mb-4 animate-bounce">
            <flux:icon.speaker-wave class="w-12 h-12" />
        </div>
        <h2 class="text-4xl font-bold mb-2">Izin Suara Diperlukan</h2>
        <p class="text-xl text-slate-300">Klik di mana saja pada layar ini untuk mengaktifkan fitur panggilan suara.</p>
    </div>

    {{-- Connection Status Indicator --}}
    <div x-show="!connected"
         x-transition
         class="fixed top-4 left-1/2 -translate-x-1/2 bg-red-500 text-white text-sm px-4 py-2 rounded-full z-50 shadow-lg">
        ⚠ Koneksi terputus, menghubungkan ulang...
    </div>

    {{-- Main Split Layout --}}
    <div class="h-full flex gap-5">

        {{-- Left Panel: Queue Info (60%) --}}
        <div class="flex flex-col h-full w-[60%] gap-4">

            {{-- Header Branding --}}
            <div class="bg-blue-900 text-white rounded-xl px-6 py-4 shrink-0">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        @if (config('institution.logo_path'))
                            <img src="{{ asset(config('institution.logo_path')) }}"
                                 alt="Logo"
                                 class="h-12 w-12 object-contain">
                        @endif
                        <h1 class="text-2xl font-bold">{{ config('institution.name', 'Pengadilan Agama') }}</h1>
                    </div>
                    <span x-data="{ time: '' }"
                          x-init="setInterval(() => { time = new Date().toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit', second: '2-digit'}) }, 1000); time = new Date().toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit', second: '2-digit'})"
                          x-text="time"
                          class="text-white/80 text-2xl font-mono tabular-nums"></span>
                </div>
                {{-- Ticker / Marquee --}}
                @if (config('institution.operating_hours'))
                    <div class="overflow-hidden mt-2 border-t border-white/20 pt-2">
                        <div class="animate-marquee whitespace-nowrap text-sm text-white/70">
                            🕐 Jam Operasional: {{ config('institution.operating_hours') }} &nbsp;&nbsp;&nbsp; |
                            &nbsp;&nbsp;&nbsp; 📍 {{ config('institution.address', '') }}
                            @if (config('institution.phone'))
                                &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp; 📞 {{ config('institution.phone') }}
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            {{-- Currently Called Section --}}
            <div class="space-y-3 shrink-0">
                <h2 class="text-blue-900 font-bold text-2xl uppercase tracking-wider">Sedang Dipanggil</h2>

                @if ($currentCalls->isEmpty())
                    <div class="bg-white rounded-2xl p-8 text-center text-slate-400 text-xl shadow-sm">
                        Belum ada panggilan
                    </div>
                @else
                    {{-- Hero Card: First/Most Recent Call --}}
                    @php $heroCall = $currentCalls->first(); @endphp
                    <div wire:key="hero-call-{{ $heroCall->id }}"
                         x-data="{ shown: false }"
                         x-init="$nextTick(() => shown = true)"
                         x-show="shown"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="bg-white rounded-2xl shadow-lg border-l-8 border-amber-500 p-6 animate-pulse-gentle">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-7xl font-black text-slate-900">{{ $heroCall->ticket_number }}</div>
                                <div class="text-3xl text-blue-900 font-semibold mt-2">{{ $heroCall->counter?->name ?? '-' }}</div>
                                <div class="text-lg text-slate-500 mt-1">{{ $heroCall->service?->name ?? '-' }}</div>
                            </div>
                            <div class="text-amber-500">
                                <svg class="w-20 h-20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- Other Called Tickets (skip first) --}}
                    @if ($currentCalls->count() > 1)
                        <div class="grid grid-cols-2 gap-3">
                            @foreach ($currentCalls->skip(1) as $ticket)
                            <div wire:key="current-call-{{ $ticket->id }}"
                                 x-data="{ shown: false }"
                                 x-init="$nextTick(() => shown = true)"
                                 x-show="shown"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 class="bg-white rounded-xl shadow-sm border-l-4 border-amber-400 p-4">
                                    <div class="text-4xl font-black text-slate-800">{{ $ticket->ticket_number }}</div>
                                    <div class="text-xl text-blue-800 font-semibold mt-1">{{ $ticket->counter?->name ?? '-' }}</div>
                                    <div class="text-sm text-slate-500 mt-0.5">{{ $ticket->service?->name ?? '-' }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>

            {{-- History Section --}}
            <div class="space-y-3 flex-1 min-h-0 overflow-hidden">
                <h2 class="text-slate-500 font-semibold text-lg uppercase tracking-wider">Riwayat</h2>

                @if ($recentCalls->isEmpty())
                    <div class="bg-white rounded-xl p-6 text-center text-slate-400 shadow-sm">
                        Belum ada riwayat hari ini
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach ($recentCalls as $ticket)
                            <div wire:key="recent-call-{{ $ticket->id }}"
                                 class="bg-white rounded-xl p-4 shadow-sm flex items-center justify-between {{ $ticket->status === \App\Enums\QueueStatus::Called ? 'border-l-4 border-amber-400' : '' }}"
                                 style="opacity: {{ max(0.4, 0.9 - ($loop->index * 0.15)) }}">
                                <div class="flex items-center gap-4">
                                    <span class="text-xl font-bold font-mono text-slate-700">{{ $ticket->ticket_number }}</span>
                                    <span class="text-lg text-slate-500">{{ $ticket->counter?->name ?? '-' }}</span>
                                </div>
                                <span class="text-lg font-mono text-slate-400">{{ $ticket->called_at?->format('H:i') ?? '-' }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        {{-- Right Panel: Video (40%) --}}
        <div wire:ignore class="w-[40%] h-full border-l border-slate-200 bg-black flex items-center justify-center rounded-xl overflow-hidden">
            <video x-show="hasVideos"
                   x-ref="videoPlayer"
                   x-init="registerVideo($el)"
                   x-on:playing="debugLog('video event playing', mediaState($el))"
                   x-on:ended="debugLog('video event ended', mediaState($el)); playNext()"
                   x-on:error="debugLog('video event error', mediaState($el))"
                   autoplay
                   muted
                   playsinline
                   preload="auto"
                   class="block h-full w-full object-contain">
            </video>

            <iframe x-show="!hasVideos"
                    src="https://www.youtube.com/embed/videoseries?list=PLillGF-RfqbZ2ybcoD2OaabW2P7Ws8CWu&autoplay=1&mute=1&loop=1"
                    class="block h-full w-full"
                    frameborder="0"
                    allow="autoplay; encrypted-media"
                    allowfullscreen>
            </iframe>
        </div>

    </div>

    {{-- Hidden Logout --}}
    <div class="fixed bottom-2 right-2 opacity-0 hover:opacity-100 transition-opacity z-50">
        <form method="POST" action="{{ route('tv-display.logout') }}">
            @csrf
            <button type="submit"
                    class="text-slate-300 text-xs px-2 py-1 rounded border border-slate-200 hover:text-slate-600 hover:border-slate-400">
                Logout
            </button>
        </form>
    </div>

</div>
