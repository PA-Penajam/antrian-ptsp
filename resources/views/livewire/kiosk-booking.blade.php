<div class="flex min-h-screen flex-col justify-between gap-6 p-4 sm:p-6 lg:p-8">
    <div class="mx-auto flex w-full max-w-5xl flex-1 items-center justify-center">
        <flux:card class="w-full border-white/10 bg-zinc-950/82 p-8 text-center shadow-[0_32px_90px_-48px_rgba(8,47,73,0.9)] backdrop-blur sm:p-10">

            {{-- Step 1: Select Service --}}
            @if ($step === 1)
                <div class="space-y-8">
                    <div class="space-y-3">
                        <flux:heading level="1" size="2xl" class="text-white">Pilih Layanan</flux:heading>
                        <flux:text class="mx-auto max-w-2xl text-lg text-zinc-400">
                            Silakan pilih layanan yang ingin Anda ambil antriannya
                        </flux:text>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @forelse($this->services as $service)
                            <button wire:click="selectService({{ $service->id }})"
                                class="rounded-2xl border-2 border-zinc-700 bg-zinc-800 p-6 text-left transition-all duration-200 hover:border-cyan-500 hover:bg-zinc-700 active:scale-95 cursor-pointer">
                                <div class="flex items-start justify-between">
                                    <div class="rounded-lg bg-cyan-500/10 px-3 py-1">
                                        <span class="text-2xl font-black text-cyan-400">{{ $service->letter_code ?? $service->code }}</span>
                                    </div>
                                </div>
                                <div class="mt-4 text-lg font-semibold text-zinc-100">{{ $service->name }}</div>
                                @if($service->description)
                                    <div class="mt-2 text-sm text-zinc-400 line-clamp-2">{{ $service->description }}</div>
                                @endif
                            </button>
                        @empty
                            <div class="col-span-full rounded-2xl border border-zinc-700 bg-zinc-800/50 p-12">
                                <flux:icon.x-circle class="mx-auto size-16 text-zinc-500" />
                                <flux:text class="mt-4 text-zinc-400">Tidak ada layanan yang tersedia saat ini</flux:text>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif

            {{-- Step 2: Fill Visitor Data --}}
            @if ($step === 2)
                <div class="mx-auto max-w-lg space-y-8">
                    <div class="space-y-3">
                        <flux:heading level="1" size="2xl" class="text-white">Isi Data Pengunjung</flux:heading>
                        <flux:text class="text-zinc-400">
                            Layanan: <span class="font-semibold text-cyan-400">{{ $this->selectedService?->name }}</span>
                        </flux:text>
                    </div>

                    <div class="space-y-6">
                        <flux:field>
                            <flux:label class="text-left text-lg text-zinc-300">Nama Lengkap <span class="text-red-400">*</span></flux:label>
                            <flux:input wire:model="visitorName" size="lg" placeholder="Nama sesuai identitas" autofocus />
                            <flux:error name="visitorName" />
                        </flux:field>

                        <flux:field>
                            <flux:label class="text-left text-lg text-zinc-300">NIK / No. Identitas</flux:label>
                            <flux:input wire:model="visitorIdentifier" size="lg" placeholder="Opsional" />
                            <flux:error name="visitorIdentifier" />
                        </flux:field>

                        <flux:field>
                            <flux:label class="text-left text-lg text-zinc-300">No. Telepon</flux:label>
                            <flux:input wire:model="visitorPhone" size="lg" placeholder="Opsional" />
                            <flux:error name="visitorPhone" />
                        </flux:field>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <flux:button wire:click="goBack" variant="ghost" class="flex-1 py-6 text-xl">
                            ← Kembali
                        </flux:button>
                        <flux:button wire:click="submitData" variant="primary" class="flex-1 py-6 text-xl">
                            Lanjut →
                        </flux:button>
                    </div>
                </div>
            @endif

            {{-- Step 3: Confirmation --}}
            @if ($step === 3)
                <div class="mx-auto max-w-lg space-y-8">
                    <div class="space-y-3">
                        <flux:heading level="1" size="2xl" class="text-white">Konfirmasi</flux:heading>
                        <flux:text class="text-zinc-400">Pastikan data Anda sudah benar</flux:text>
                    </div>

                    <div class="rounded-2xl border border-zinc-700 bg-zinc-800/50 p-6 text-left">
                        <div class="space-y-4">
                            <div>
                                <div class="text-sm text-zinc-500">Layanan</div>
                                <div class="text-xl font-semibold text-zinc-100">{{ $this->selectedService?->name }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-zinc-500">Nama</div>
                                <div class="text-xl font-semibold text-zinc-100">{{ $visitorName }}</div>
                            </div>
                            @if($visitorIdentifier)
                                <div>
                                    <div class="text-sm text-zinc-500">NIK / Identitas</div>
                                    <div class="text-lg text-zinc-300">{{ $visitorIdentifier }}</div>
                                </div>
                            @endif
                            @if($visitorPhone)
                                <div>
                                    <div class="text-sm text-zinc-500">Telepon</div>
                                    <div class="text-lg text-zinc-300">{{ $visitorPhone }}</div>
                                </div>
                            @endif
                            <div class="border-t border-zinc-700 pt-4">
                                <div class="text-sm text-zinc-500">Tanggal</div>
                                <div class="text-lg text-zinc-300">{{ now()->translatedFormat('d F Y') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <flux:button wire:click="goBack" variant="ghost" class="flex-1 py-6 text-xl">
                            ← Kembali
                        </flux:button>
                        <flux:button wire:click="confirmBooking" variant="primary" class="flex-1 py-6 text-xl">
                            Cetak Tiket
                        </flux:button>
                    </div>
                </div>
            @endif

            {{-- Step 4: Ticket Printed --}}
            @if ($step === 4 && $ticket)
                <div class="mx-auto max-w-md space-y-8"
                    x-data="{ countdown: 30 }"
                    x-init="setInterval(() => { countdown--; if(countdown <= 0) $wire.resetWizard(); }, 1000)">

                    <div class="space-y-3">
                        <flux:badge color="green" size="lg" class="mx-auto">Tiket Berhasil Dibuat</flux:badge>
                        <flux:heading level="1" size="2xl" class="text-white">Nomor Antrian Anda</flux:heading>
                    </div>

                    {{-- Ticket Card --}}
                    <div class="rounded-3xl border-2 border-cyan-500/50 bg-gradient-to-b from-zinc-800 to-zinc-900 p-8">
                        <div class="space-y-6">
                            <div class="text-7xl font-black tracking-wider text-white sm:text-8xl">
                                {{ $ticket->ticket_number }}
                            </div>

                            <div class="border-t border-zinc-700 pt-6">
                                <div class="text-lg text-zinc-300">{{ $ticket->service?->name }}</div>
                                <div class="mt-1 text-sm text-zinc-500">{{ $ticket->service_date?->translatedFormat('d F Y') }}</div>
                            </div>

                            {{-- Barcode placeholder --}}
                            <div class="flex justify-center py-4">
                                <div class="h-16 w-48 rounded bg-zinc-700"></div>
                            </div>

                            <div class="text-xs text-zinc-600">
                                Mohon tunggu panggilan dengan nomor antrian Anda
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <flux:text class="text-zinc-500">
                            Kembali ke halaman utama dalam <span x-text="countdown" class="font-mono text-lg text-cyan-400"></span> detik
                        </flux:text>

                        <flux:button wire:click="resetWizard" variant="primary" class="w-full py-6 text-xl">
                            Ambil Tiket Baru
                        </flux:button>
                    </div>
                </div>
            @endif

        </flux:card>
    </div>

    {{-- Footer / Logout --}}
    <div class="mx-auto w-full max-w-5xl">
        <form method="POST" action="{{ route('kiosk.logout') }}" class="flex justify-end">
            @csrf
            <flux:button type="submit" variant="ghost" icon="arrow-right-start-on-rectangle" class="rounded-2xl border border-white/10 bg-zinc-900/70 px-6 py-4 text-lg text-zinc-100">
                Keluar
            </flux:button>
        </form>
    </div>
</div>
