<div class="flex min-h-screen flex-col justify-between gap-6 p-4 sm:p-6 lg:p-8 {{ $fontSize === 'large' ? 'text-lg' : 'text-base' }}">
    <div class="mx-auto flex w-full max-w-5xl flex-1 items-center justify-center">
        <flux:card class="w-full border-slate-200 bg-white/90 p-8 text-center shadow-xl backdrop-blur sm:p-10">

            {{-- Step 1: Select Service --}}
            @if ($step === 1)
                <div wire:key="kiosk-step-1" class="space-y-10">
                    {{-- Header dengan Visual Hierarchy yang Kuat --}}
                    <div class="space-y-4">
                        <div class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-cyan-500 to-blue-600 px-6 py-2 shadow-lg">
                            <flux:icon.queue-list class="size-5 text-white" />
                            <span class="ml-2 text-base font-semibold text-white">Langkah 1 dari 4</span>
                        </div>
                        <flux:heading level="1" size="3xl" class="bg-gradient-to-r from-slate-900 to-slate-700 bg-clip-text text-4xl font-black text-transparent">
                            Pilih Layanan
                        </flux:heading>
                        <flux:text class="mx-auto max-w-2xl text-xl leading-relaxed text-slate-600">
                            Silakan pilih layanan yang ingin Anda ambil antriannya
                        </flux:text>
                    </div>

                    {{-- Grid Layanan dengan Card yang Eye-Catching --}}
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @forelse($this->services as $index => $service)
                            @php
                                $colors = [
                                    ['from-cyan-400 to-blue-500', 'bg-cyan-50', 'text-cyan-600', 'border-cyan-200'],
                                    ['from-emerald-400 to-teal-500', 'bg-emerald-50', 'text-emerald-600', 'border-emerald-200'],
                                    ['from-violet-400 to-purple-500', 'bg-violet-50', 'text-violet-600', 'border-violet-200'],
                                    ['from-amber-400 to-orange-500', 'bg-amber-50', 'text-amber-600', 'border-amber-200'],
                                    ['from-rose-400 to-pink-500', 'bg-rose-50', 'text-rose-600', 'border-rose-200'],
                                    ['from-indigo-400 to-blue-500', 'bg-indigo-50', 'text-indigo-600', 'border-indigo-200'],
                                ];
                                $color = $colors[$index % count($colors)];
                            @endphp
                            
                            <button wire:click="selectService({{ $service->id }})"
                                class="group relative cursor-pointer overflow-hidden rounded-3xl border-2 border-slate-200 bg-white p-0 text-left shadow-md transition-[transform,border-color,box-shadow] duration-100 ease-out hover:border-slate-300 hover:shadow-xl active:translate-y-px active:scale-[0.99] focus-visible:ring-2 focus-visible:ring-cyan-400 focus-visible:ring-offset-2">
                                
                                {{-- Header Card dengan Gradient --}}
                                <div class="relative h-28 bg-gradient-to-br {{ $color[0] }} p-6">
                                    {{-- Pattern Background --}}
                                    <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 20px 20px;"></div>
                                    
                                    {{-- Service Code Badge --}}
                                    <div class="relative flex items-center justify-between">
                                        <div class="rounded-2xl bg-white/95 px-5 py-3 shadow-lg backdrop-blur-sm">
                                            <span class="text-4xl font-black {{ $color[2] }}">{{ $service->letter_code ?? $service->code }}</span>
                                        </div>
                                        {{-- Arrow Icon --}}
                                        <div class="rounded-full bg-white/20 p-3 text-white backdrop-blur-sm transition-colors group-hover:bg-white/30">
                                            <flux:icon.arrow-right class="size-6" />
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Content Area --}}
                                <div class="p-6">
                                    <div class="text-2xl font-bold text-slate-800 transition-colors group-hover:text-slate-900">
                                        {{ $service->name }}
                                    </div>
                                    @if($service->description)
                                        <div class="mt-3 text-base leading-relaxed text-slate-500 line-clamp-2">
                                            {{ $service->description }}
                                        </div>
                                    @endif
                                    
                                    {{-- CTA Button --}}
                                    <div class="mt-5 flex items-center justify-between">
                                        <div class="flex items-center gap-2 rounded-full {{ $color[1] }} px-4 py-2">
                                            <flux:icon.ticket class="size-4 {{ $color[2] }}" />
                                            <span class="text-sm font-semibold {{ $color[2] }}">Ambil Antrian</span>
                                        </div>
                                        <div class="rounded-full bg-slate-100 p-2 text-slate-400 transition-colors group-hover:bg-slate-800 group-hover:text-white">
                                            <flux:icon.chevron-right class="size-5" />
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Bottom Accent Line --}}
                                <div class="absolute bottom-0 left-0 h-1 w-0 bg-gradient-to-r {{ $color[0] }} transition-all group-hover:w-full"></div>
                            </button>
                        @empty
                            <div class="col-span-full rounded-3xl border-2 border-dashed border-slate-300 bg-slate-50/50 p-16 text-center">
                                <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-slate-100">
                                    <flux:icon.inbox class="size-12 text-slate-400" />
                                </div>
                                <flux:heading level="2" size="xl" class="mt-6 text-slate-700">Tidak Ada Layanan Tersedia</flux:heading>
                                <flux:text class="mt-2 text-lg text-slate-500">Silakan hubungi petugas untuk informasi lebih lanjut</flux:text>
                            </div>
                        @endforelse
                    </div>
                    
                    {{-- Helper Text --}}
                    <div class="flex items-center justify-center gap-2 rounded-2xl bg-slate-50 py-4 text-slate-500">
                        <flux:icon.hand-raised class="size-5" />
                        <span class="text-base">Ketuk kartu layanan untuk melanjutkan</span>
                    </div>
                </div>
            @endif

            {{-- Step 2: Fill Visitor Data --}}
            @if ($step === 2)
                <div wire:key="kiosk-step-2" class="mx-auto max-w-xl space-y-8">
                    {{-- Header dengan Progress --}}
                    <div class="space-y-4">
                        <div class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 px-6 py-2 shadow-lg">
                            <flux:icon.user class="size-5 text-white" />
                            <span class="ml-2 text-base font-semibold text-white">Langkah 2 dari 4</span>
                        </div>
                        <flux:heading level="1" size="3xl" class="bg-gradient-to-r from-slate-900 to-slate-700 bg-clip-text text-4xl font-black text-transparent">
                            Isi Data Pengunjung
                        </flux:heading>
                        
                        {{-- Selected Service Badge --}}
                        <div class="flex items-center justify-center gap-3 rounded-2xl bg-gradient-to-r from-cyan-50 to-blue-50 p-4">
                            <div class="rounded-xl bg-cyan-100 px-4 py-2">
                                <span class="text-2xl font-black text-cyan-600">{{ $this->selectedService?->letter_code ?? $this->selectedService?->code }}</span>
                            </div>
                            <div class="text-left">
                                <div class="text-sm text-slate-500">Layanan Terpilih</div>
                                <div class="text-lg font-bold text-slate-800">{{ $this->selectedService?->name }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Form Fields dengan Styling yang Lebih Baik --}}
                    <div class="space-y-6 rounded-3xl bg-white p-8 shadow-lg">
                        <flux:field>
                            <flux:label class="flex items-center gap-2 text-left text-xl font-semibold text-slate-800">
                                <flux:icon.user-circle class="size-5 text-cyan-600" />
                                Nama Lengkap
                                <span class="rounded-full bg-rose-100 px-2 py-0.5 text-sm font-bold text-rose-600">Wajib</span>
                            </flux:label>
                            <flux:input
                                wire:model="visitorName"
                                size="lg"
                                placeholder="Masukkan nama sesuai identitas"
                                autofocus
                                class="mt-3 h-16 text-xl [&_[data-flux-control]]:h-16 [&_[data-flux-control]]:rounded-2xl [&_[data-flux-control]]:border-2 [&_[data-flux-control]]:border-slate-200 [&_[data-flux-control]]:text-xl [&_[data-flux-control]]:focus:border-cyan-500"
                            />
                            <flux:error name="visitorName" />
                        </flux:field>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <flux:field>
                                <flux:label class="flex items-center gap-2 text-left text-lg font-medium text-slate-700">
                                    <flux:icon.identification class="size-5 text-slate-400" />
                                    NIK / No. Identitas
                                </flux:label>
                                <flux:input
                                    wire:model="visitorIdentifier"
                                    size="lg"
                                    placeholder="Opsional"
                                    class="mt-2 h-14 text-lg [&_[data-flux-control]]:h-14 [&_[data-flux-control]]:rounded-xl [&_[data-flux-control]]:border-2 [&_[data-flux-control]]:border-slate-200 [&_[data-flux-control]]:text-lg"
                                />
                                <flux:error name="visitorIdentifier" />
                            </flux:field>

                            <flux:field>
                                <flux:label class="flex items-center gap-2 text-left text-lg font-medium text-slate-700">
                                    <flux:icon.phone class="size-5 text-slate-400" />
                                    No. Telepon
                                </flux:label>
                                <flux:input
                                    wire:model="visitorPhone"
                                    size="lg"
                                    placeholder="Opsional"
                                    type="tel"
                                    class="mt-2 h-14 text-lg [&_[data-flux-control]]:h-14 [&_[data-flux-control]]:rounded-xl [&_[data-flux-control]]:border-2 [&_[data-flux-control]]:border-slate-200 [&_[data-flux-control]]:text-lg"
                                />
                                <flux:error name="visitorPhone" />
                            </flux:field>
                        </div>

                        <flux:field>
                            <flux:label class="flex items-center gap-2 text-left text-lg font-medium text-slate-700">
                                <flux:icon.map class="size-5 text-slate-400" />
                                Kelurahan / Desa
                                <span class="rounded-full bg-rose-100 px-2 py-0.5 text-sm font-bold text-rose-600">Wajib</span>
                            </flux:label>
                            @if ($this->wilayahOptions->isEmpty())
                                <div class="mt-2 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                    Kelurahan/desa belum tersedia. Pastikan admin sudah memilih kabupaten aktif di menu Setting Wilayah.
                                </div>
                            @else
                                <flux:select
                                    wire:model="visitorWilayahKode"
                                    size="lg"
                                    placeholder="Pilih kelurahan/desa"
                                    class="mt-2 [&_[data-flux-control]]:h-14 [&_[data-flux-control]]:rounded-xl [&_[data-flux-control]]:border-2 [&_[data-flux-control]]:border-slate-200 [&_[data-flux-control]]:text-lg"
                                >
                                    @foreach ($this->wilayahOptions as $wilayah)
                                        <flux:select.option value="{{ $wilayah->kode }}">
                                            {{ $wilayah->nama }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>
                            @endif

                            <flux:error name="visitorWilayahKode" />
                        </flux:field>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex gap-4 pt-4">
                        <flux:button wire:click="goBack" variant="outline" icon="arrow-left" class="h-16 flex-1 rounded-2xl border-2 border-slate-300 text-xl font-semibold text-slate-700 transition-colors hover:border-slate-400 hover:bg-slate-50">
                            Kembali
                        </flux:button>
                        <flux:button wire:click="submitData" variant="primary" icon="arrow-right" iconTrailing class="h-16 flex-1 rounded-2xl bg-gradient-to-r from-cyan-600 to-blue-600 text-xl font-bold shadow-lg shadow-cyan-500/25 transition-shadow hover:shadow-xl hover:shadow-cyan-500/30" wire:loading.attr="disabled" wire:target="submitData">
                            <span wire:loading.remove wire:target="submitData">Lanjutkan</span>
                            <span wire:loading wire:target="submitData" class="inline-flex items-center gap-2">
                                <svg class="h-6 w-6 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Memproses...
                            </span>
                        </flux:button>
                    </div>
                </div>
            @endif

            {{-- Step 3: Confirmation --}}
            @if ($step === 3)
                <div wire:key="kiosk-step-3" class="mx-auto max-w-xl space-y-8">
                    {{-- Header --}}
                    <div class="space-y-4">
                        <div class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-amber-500 to-orange-600 px-6 py-2 shadow-lg">
                            <flux:icon.clipboard-document-check class="size-5 text-white" />
                            <span class="ml-2 text-base font-semibold text-white">Langkah 3 dari 4</span>
                        </div>
                        <flux:heading level="1" size="3xl" class="bg-gradient-to-r from-slate-900 to-slate-700 bg-clip-text text-4xl font-black text-transparent">
                            Konfirmasi Data
                        </flux:heading>
                        <flux:text class="text-xl text-slate-600">
                            Pastikan data Anda sudah benar sebelum mencetak tiket
                        </flux:text>
                    </div>

                    {{-- Summary Card dengan Design yang Lebih Menarik --}}
                    <div class="overflow-hidden rounded-3xl border-2 border-slate-200 bg-white shadow-xl">
                        {{-- Header dengan Gradient --}}
                        <div class="bg-gradient-to-r from-slate-800 to-slate-700 px-8 py-5">
                            <div class="flex items-center gap-3">
                                <div class="rounded-xl bg-white/20 p-2 backdrop-blur-sm">
                                    <flux:icon.ticket class="size-6 text-white" />
                                </div>
                                <span class="text-lg font-bold text-white">Detail Antrian</span>
                            </div>
                        </div>
                        
                        {{-- Content --}}
                        <div class="p-8">
                            <div class="space-y-6">
                                {{-- Service Info --}}
                                <div class="flex items-start gap-4 rounded-2xl bg-gradient-to-r from-cyan-50 to-blue-50 p-5">
                                    <div class="rounded-xl bg-cyan-100 px-4 py-2">
                                        <span class="text-2xl font-black text-cyan-600">{{ $this->selectedService?->letter_code ?? $this->selectedService?->code }}</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-slate-500">Layanan</div>
                                        <div class="text-xl font-bold text-slate-900">{{ $this->selectedService?->name }}</div>
                                    </div>
                                </div>
                                
                                {{-- Visitor Info --}}
                                <div class="grid gap-6 sm:grid-cols-2">
                                    <div class="rounded-2xl bg-slate-50 p-5">
                                        <div class="flex items-center gap-2 text-sm font-medium text-slate-500">
                                            <flux:icon.user class="size-4" />
                                            Nama Lengkap
                                        </div>
                                        <div class="mt-2 text-2xl font-bold text-slate-900">{{ $visitorName }}</div>
                                    </div>
                                    
                                    @if($visitorIdentifier)
                                        <div class="rounded-2xl bg-slate-50 p-5">
                                            <div class="flex items-center gap-2 text-sm font-medium text-slate-500">
                                                <flux:icon.identification class="size-4" />
                                                NIK / Identitas
                                            </div>
                                            <div class="mt-2 text-xl font-semibold text-slate-800">{{ $visitorIdentifier }}</div>
                                        </div>
                                    @endif
                                    
                                    @if($visitorPhone)
                                        <div class="rounded-2xl bg-slate-50 p-5">
                                            <div class="flex items-center gap-2 text-sm font-medium text-slate-500">
                                                <flux:icon.phone class="size-4" />
                                                No. Telepon
                                            </div>
                                            <div class="mt-2 text-xl font-semibold text-slate-800">{{ $visitorPhone }}</div>
                                        </div>
                                    @endif
                                    
                                    <div class="rounded-2xl bg-slate-50 p-5">
                                        <div class="flex items-center gap-2 text-sm font-medium text-slate-500">
                                            <flux:icon.calendar class="size-4" />
                                            Tanggal Kunjungan
                                        </div>
                                        <div class="mt-2 text-xl font-semibold text-slate-800">{{ now()->translatedFormat('d F Y') }}</div>
                                    </div>

                                    <div class="rounded-2xl bg-slate-50 p-5 sm:col-span-2">
                                        <div class="flex items-center gap-2 text-sm font-medium text-slate-500">
                                            <flux:icon.map class="size-4" />
                                            Kelurahan / Desa
                                        </div>
                                        <div class="mt-2 text-lg font-semibold text-slate-800">
                                            {{ $visitorWilayahNama ?: $visitorWilayahKode }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex gap-4 pt-4">
                        <flux:button wire:click="goBack" variant="outline" icon="arrow-left" class="h-16 flex-1 rounded-2xl border-2 border-slate-300 text-xl font-semibold text-slate-700 transition-colors hover:border-slate-400 hover:bg-slate-50">
                            Kembali
                        </flux:button>
                        <flux:button wire:click="confirmBooking" variant="primary" icon="printer" class="h-16 flex-1 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 text-xl font-bold shadow-lg shadow-emerald-500/25 transition-shadow hover:shadow-xl hover:shadow-emerald-500/30" wire:loading.attr="disabled" wire:target="confirmBooking">
                            <span wire:loading.remove wire:target="confirmBooking">Cetak Tiket</span>
                            <span wire:loading wire:target="confirmBooking" class="inline-flex items-center gap-2">
                                <svg class="h-6 w-6 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Memproses...
                            </span>
                        </flux:button>
                    </div>
                </div>
            @endif

            {{-- Step 4: Ticket Printed --}}
            @if ($step === 4 && $ticket)
                <div wire:key="kiosk-step-4" class="mx-auto max-w-lg space-y-8"
                    x-data="{ countdown: 30 }"
                    x-init="setInterval(() => { countdown--; if(countdown <= 0) $wire.resetWizard(); }, 1000)">

                    {{-- Success Header dengan Animation --}}
                    <div class="space-y-4">
                        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 to-green-500 shadow-lg shadow-emerald-500/30"
                            x-data="{ show: false }"
                            x-init="setTimeout(() => show = true, 100)"
                            x-show="show"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 scale-50"
                            x-transition:enter-end="opacity-100 scale-100">
                            <flux:icon.check class="size-10 text-white" />
                        </div>
                        
                        <flux:badge color="green" size="lg" class="mx-auto bg-gradient-to-r from-emerald-500 to-green-600 px-6 py-2 text-base font-bold text-white shadow-lg shadow-emerald-500/25">
                            Tiket Berhasil Dibuat
                        </flux:badge>
                        
                        <flux:heading level="1" size="3xl" class="bg-gradient-to-r from-slate-900 to-slate-700 bg-clip-text text-4xl font-black text-transparent">
                            Nomor Antrian Anda
                        </flux:heading>
                    </div>

                    {{-- Ticket Card dengan Design Premium --}}
                    <div class="relative overflow-hidden rounded-3xl border-2 border-slate-200 bg-white p-10 shadow-2xl">
                        {{-- Background Pattern --}}
                        <div class="absolute inset-0 opacity-5" style="background-image: radial-gradient(circle at 2px 2px, #0ea5e9 1px, transparent 0); background-size: 24px 24px;"></div>
                        
                        {{-- Top Accent Line --}}
                        <div class="absolute left-0 right-0 top-0 h-2 bg-gradient-to-r from-cyan-500 via-blue-500 to-violet-500"></div>
                        
                        <div class="relative space-y-8">
                            {{-- Ticket Number dengan Typography Bold --}}
                            <div class="text-center">
                                <div class="bg-gradient-to-br from-cyan-600 via-blue-600 to-violet-600 bg-clip-text text-9xl font-black tracking-wider text-transparent drop-shadow-sm sm:text-[10rem]">
                                    {{ $ticket->ticket_number }}
                                </div>
                            </div>

                            {{-- Service Info --}}
                            <div class="rounded-2xl bg-gradient-to-r from-slate-50 to-slate-100 p-6 text-center">
                                <div class="text-sm font-medium uppercase tracking-wide text-slate-500">Layanan</div>
                                <div class="mt-1 text-2xl font-bold text-slate-800">{{ $ticket->service?->name }}</div>
                                <div class="mt-3 flex items-center justify-center gap-2 text-slate-500">
                                    <flux:icon.calendar class="size-4" />
                                    <span class="text-lg">{{ $ticket->service_date?->translatedFormat('d F Y') }}</span>
                                </div>
                            </div>

                            {{-- Barcode --}}
                            <div class="flex justify-center rounded-2xl bg-slate-50 py-6" wire:init="loadBarcode">
                                @if ($barcodeSvg)
                                    <div class="barcode-container">{!! $barcodeSvg !!}</div>
                                @else
                                    <div class="h-16 w-48 rounded-lg bg-slate-200"></div>
                                @endif
                            </div>

                            {{-- Instructions --}}
                            <div class="flex items-center justify-center gap-3 rounded-2xl bg-amber-50 p-4 text-center">
                                <flux:icon.speaker-wave class="size-5 text-amber-600" />
                                <span class="text-base font-medium text-amber-800">
                                    Mohon tunggu panggilan nomor antrian Anda
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Footer Actions --}}
                    <div class="space-y-4">
                        <div class="flex items-center justify-center gap-2 rounded-2xl bg-slate-100 py-3 text-slate-600">
                            <flux:icon.clock class="size-5" />
                            <span class="text-base">
                                Kembali ke menu dalam <span x-text="countdown" class="font-mono text-lg font-bold text-cyan-600"></span> detik
                            </span>
                        </div>

                        <flux:button wire:click="resetWizard" variant="primary" icon="plus-circle" class="h-16 w-full rounded-2xl bg-gradient-to-r from-cyan-600 to-blue-600 text-xl font-bold shadow-lg shadow-cyan-500/25 transition-shadow hover:shadow-xl">
                            Ambil Tiket Baru
                        </flux:button>
                    </div>
                </div>
            @endif

        </flux:card>
    </div>

    {{-- Footer --}}
    <div class="mx-auto flex w-full max-w-5xl items-center justify-between">
        <form method="POST" action="{{ route('kiosk.logout') }}">
            @csrf
            <flux:button type="submit" variant="danger" icon="arrow-right-start-on-rectangle" class="h-14 rounded-2xl px-8 text-lg font-semibold transition-colors">
                Keluar Kiosk
            </flux:button>
        </form>

        <flux:button wire:click="toggleFontSize" variant="ghost" icon="{{ $fontSize === 'large' ? 'minus' : 'plus' }}" class="h-14 rounded-2xl border border-slate-200 bg-white/70 px-6 text-base text-slate-600 shadow-sm transition-colors">
            {{ $fontSize === 'large' ? 'Teks Normal' : 'Teks Besar' }}
        </flux:button>
    </div>
</div>
