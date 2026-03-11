<flux:main container>
    @php
        $currentCallsPayload = $currentCalls
            ->map(fn ($ticket) => [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'counter_name' => $ticket->counter?->name,
                'called_at' => $ticket->called_at?->toIso8601String(),
            ])
            ->values();
    @endphp

    <div wire:poll.5000ms class="mx-auto max-w-5xl space-y-8">
        <div class="flex flex-col gap-4 text-center sm:flex-row sm:items-center sm:justify-between sm:text-left">
            <div class="space-y-2">
                <flux:heading size="xl" level="1">Display Antrian PTSP</flux:heading>
                <flux:text class="text-base text-slate-600">Informasi panggilan antrian {{ config('institution.name') }} yang diperbarui otomatis setiap 5 detik.</flux:text>
            </div>

            <button
                id="tts-toggle"
                type="button"
                hidden
                onclick="window.toggleQueueDisplayTts()"
                class="inline-flex items-center justify-center gap-2 self-center rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-amber-300 hover:text-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-300 sm:self-auto"
                aria-pressed="false"
            >
                <span id="tts-off-icon">🔇 Aktifkan Suara</span>
                <span id="tts-on-icon" class="hidden">🔊 Suara Aktif</span>
            </button>
        </div>

        <flux:card class="space-y-4 border-amber-200 bg-amber-50/60">
            <div class="flex items-center justify-between gap-3">
                <flux:heading size="lg" icon="megaphone">Sedang Dipanggil</flux:heading>
                <flux:badge color="amber" inset="top bottom">Live</flux:badge>
            </div>

            @if ($currentCalls->isNotEmpty())
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($currentCalls as $ticket)
                        <flux:card wire:key="current-call-{{ $ticket->id }}" class="space-y-3 border-amber-300 bg-white text-center shadow-sm">
                            <flux:heading size="xl" class="text-amber-700">{{ $ticket->ticket_number }}</flux:heading>
                            <flux:text class="text-sm text-slate-600">Nomor antrian aktif</flux:text>
                            <div class="flex justify-center">
                                <flux:badge size="sm" color="amber">{{ $ticket->counter?->name ?? 'Loket belum ditetapkan' }}</flux:badge>
                            </div>
                        </flux:card>
                    @endforeach
                </div>
            @else
                <flux:text class="text-slate-500">Tidak ada panggilan aktif saat ini.</flux:text>
            @endif
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="lg" icon="clock">Riwayat Panggilan</flux:heading>

            @if ($recentCalls->isNotEmpty())
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Nomor Antrian</flux:table.column>
                        <flux:table.column>Loket</flux:table.column>
                        <flux:table.column>Waktu Panggilan</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($recentCalls as $ticket)
                            <flux:table.row wire:key="recent-call-{{ $ticket->id }}">
                                <flux:table.cell>
                                    <flux:text class="font-semibold text-slate-900">{{ $ticket->ticket_number }}</flux:text>
                                </flux:table.cell>
                                <flux:table.cell>{{ $ticket->counter?->name ?? '-' }}</flux:table.cell>
                                <flux:table.cell>{{ $ticket->called_at?->format('H:i:s') ?? '-' }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @else
                <flux:text class="text-slate-500">Belum ada riwayat panggilan.</flux:text>
            @endif
        </flux:card>

        <template id="current-calls-data">@json($currentCallsPayload)</template>
    </div>

    <script>
        (() => {
            const elevenLabsEndpoint = "{{ route('tv-display.tts.announcement') }}";
            const elevenLabsConfigured = Boolean(Number("{{ filled(config('services.elevenlabs.api_key')) && filled(config('services.elevenlabs.voice_id')) ? 1 : 0 }}"));

            const state = window.queueDisplayTts ??= {
                announced: new Map(),
                ttsEnabled: false,
                hooksRegistered: false,
                lastProcessedPayload: null,
                playbackQueue: Promise.resolve(),
                currentAudio: null,
            };

            const supportsSpeech = () => 'speechSynthesis' in window && 'SpeechSynthesisUtterance' in window;

            const getCurrentCallsPayload = () => {
                const dataElement = document.getElementById('current-calls-data');

                return dataElement?.textContent?.trim() ?? '[]';
            };

            const getCurrentCalls = () => {
                try {
                    const tickets = JSON.parse(getCurrentCallsPayload());

                    return Array.isArray(tickets) ? tickets : [];
                } catch {
                    return [];
                }
            };

            const buildAnnouncementText = (ticket) => ticket.counter_name
                ? `Nomor antrian ${ticket.ticket_number}, silakan menuju ${ticket.counter_name}`
                : `Nomor antrian ${ticket.ticket_number}, harap segera menuju loket petugas`;

            const stopCurrentAudio = () => {
                if (!state.currentAudio) {
                    return;
                }

                state.currentAudio.pause();
                state.currentAudio.currentTime = 0;
                state.currentAudio = null;
            };

            const playAudioUrl = (audioUrl) => new Promise((resolve, reject) => {
                const audio = new Audio(audioUrl);

                audio.preload = 'auto';
                state.currentAudio = audio;

                const cleanup = () => {
                    if (state.currentAudio === audio) {
                        state.currentAudio = null;
                    }
                };

                audio.addEventListener('ended', () => {
                    cleanup();
                    resolve(true);
                }, { once: true });

                audio.addEventListener('error', () => {
                    cleanup();
                    reject(new Error('Audio playback failed.'));
                }, { once: true });

                audio.play().catch((error) => {
                    cleanup();
                    reject(error);
                });
            });

            const fetchElevenLabsAudioUrl = async (text) => {
                if (!elevenLabsConfigured || !text) {
                    return null;
                }

                const query = new URLSearchParams({ text });
                const response = await fetch(`${elevenLabsEndpoint}?${query.toString()}`, {
                    headers: {
                        Accept: 'application/json',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    return null;
                }

                const payload = await response.json();

                if (payload?.provider !== 'elevenlabs' || typeof payload.audio_url !== 'string' || payload.audio_url === '') {
                    return null;
                }

                return payload.audio_url;
            };

            const findIndonesianVoice = () => {
                if (!supportsSpeech()) {
                    return null;
                }

                return window.speechSynthesis
                    .getVoices()
                    .find((voice) => voice.lang.toLowerCase().startsWith('id')) ?? null;
            };

            const speakWithBrowser = async (text) => {
                if (!supportsSpeech() || !text) {
                    return;
                }

                await new Promise((resolve) => {
                    const utterance = new SpeechSynthesisUtterance(text);
                    const indonesianVoice = findIndonesianVoice();

                    utterance.lang = 'id-ID';
                    utterance.rate = 0.85;

                    if (indonesianVoice) {
                        utterance.voice = indonesianVoice;
                    }

                    utterance.addEventListener('end', () => resolve(true), { once: true });
                    utterance.addEventListener('error', () => resolve(true), { once: true });

                    window.speechSynthesis.speak(utterance);
                });
            };

            const speakTicket = async (ticket) => {
                const announcementText = buildAnnouncementText(ticket);

                try {
                    const audioUrl = await fetchElevenLabsAudioUrl(announcementText);

                    if (audioUrl) {
                        await playAudioUrl(audioUrl);

                        return;
                    }
                } catch {
                }

                await speakWithBrowser(announcementText);
            };

            const syncToggleState = () => {
                const toggleButton = document.getElementById('tts-toggle');
                const offIcon = document.getElementById('tts-off-icon');
                const onIcon = document.getElementById('tts-on-icon');

                if (!toggleButton || !offIcon || !onIcon) {
                    return;
                }

                const isSupported = supportsSpeech() || elevenLabsConfigured;

                toggleButton.hidden = !isSupported;
                toggleButton.setAttribute('aria-pressed', state.ttsEnabled ? 'true' : 'false');
                offIcon.classList.toggle('hidden', state.ttsEnabled);
                onIcon.classList.toggle('hidden', !state.ttsEnabled);
            };

            const processCurrentCalls = (shouldAnnounce) => {
                const payload = getCurrentCallsPayload();

                if (state.lastProcessedPayload === payload) {
                    syncToggleState();

                    return;
                }

                state.lastProcessedPayload = payload;

                getCurrentCalls().forEach((ticket) => {
                    const ticketId = String(ticket.id);
                    const calledAt = ticket.called_at ?? null;
                    const previousCalledAt = state.announced.get(ticketId);
                    const hasChanged = previousCalledAt === undefined || previousCalledAt !== calledAt;

                    if (hasChanged) {
                        state.announced.set(ticketId, calledAt);

                        if (shouldAnnounce) {
                            state.playbackQueue = state.playbackQueue
                                .then(() => speakTicket(ticket))
                                .catch(() => undefined);
                        }
                    }
                });

                syncToggleState();
            };

            const handleQueueDisplayUpdate = () => {
                processCurrentCalls(state.ttsEnabled && (supportsSpeech() || elevenLabsConfigured));
            };

            window.toggleQueueDisplayTts = () => {
                state.ttsEnabled = !state.ttsEnabled;
                stopCurrentAudio();

                if (supportsSpeech()) {
                    window.speechSynthesis.cancel();

                    if (state.ttsEnabled) {
                        const activationUtterance = new SpeechSynthesisUtterance('');

                        activationUtterance.volume = 0;
                        window.speechSynthesis.speak(activationUtterance);
                    }
                }

                syncToggleState();
            };

            if (!state.hooksRegistered) {
                document.addEventListener('livewire:update', handleQueueDisplayUpdate);
                state.hooksRegistered = true;
            }

            processCurrentCalls(false);

            if (supportsSpeech()) {
                window.speechSynthesis.onvoiceschanged = syncToggleState;
            }
        })();
    </script>
</flux:main>
