<?php

namespace App\Livewire;

use App\Actions\Queue\CreateQueueTicket;
use App\Enums\QueueStatus;
use App\Models\AppSetting;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\Wilayah;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Picqer\Barcode\Renderers\SvgRenderer;
use Picqer\Barcode\Types\TypeCode128;

#[Layout('layouts.kiosk')]
#[Title('Ambil Antrian Mandiri')]
class KioskBooking extends Component
{
    public int $step = 1;

    public ?int $selectedServiceId = null;

    public string $visitorName = '';

    public string $visitorIdentifier = '';

    public string $visitorPhone = '';

    public ?string $visitorWilayahKode = null;

    public string $visitorWilayahNama = '';

    public ?QueueTicket $ticket = null;

    public string $fontSize = 'normal';

    public string $barcodeSvg = '';

    public string $reprintQuery = '';

    public ?QueueTicket $reprintTicket = null;

    public string $reprintBarcodeSvg = '';

    #[Computed(persist: true, seconds: 600)]
    public function services(): Collection
    {
        return Service::active()
            ->where('walk_in_enabled', true)
            ->get();
    }

    #[Computed]
    public function selectedService(): ?Service
    {
        if ($this->selectedServiceId === null) {
            return null;
        }

        /** @var ?Service $service */
        $service = $this->services->firstWhere('id', $this->selectedServiceId);

        return $service;
    }

    #[Computed(persist: true)]
    public function wilayahOptions(): Collection
    {
        $selectedKabupatenKode = $this->selectedKabupatenKode();
        if ($selectedKabupatenKode === null) {
            return new Collection;
        }

        return Wilayah::query()
            ->whereRaw('LENGTH(kode) = 13')
            ->where('kode', 'like', "{$selectedKabupatenKode}.%")
            ->orderBy('nama')
            ->get();
    }

    public function selectService(int $serviceId): void
    {
        $this->selectedServiceId = $serviceId;
        $this->step = 2;
    }

    public function goBack(): void
    {
        if ($this->step === 2) {
            $this->selectedServiceId = null;
        }
        $this->step = max(1, $this->step - 1);
    }

    public function toggleFontSize(): void
    {
        $this->fontSize = $this->fontSize === 'normal' ? 'large' : 'normal';
    }

    public function selectWilayah(string $kode, string $nama): void
    {
        $this->visitorWilayahKode = $kode;
        $this->visitorWilayahNama = $nama;
    }

    public function updatedVisitorWilayahKode(?string $kode): void
    {
        if ($kode === null || $kode === '') {
            $this->visitorWilayahNama = '';

            return;
        }

        $this->visitorWilayahNama = (string) $this->wilayahOptions
            ->firstWhere('kode', $kode)?->nama;
    }

    public function submitData(): void
    {
        $this->validate([
            'visitorName' => ['required', 'string', 'min:3', 'max:255'],
            'visitorIdentifier' => ['nullable', 'string', 'max:50'],
            'visitorPhone' => ['nullable', 'string', 'max:20'],
            'visitorWilayahKode' => ['required', 'string', $this->wilayahExistsRule()],
        ]);

        if ($this->visitorWilayahNama === '' && $this->visitorWilayahKode !== null) {
            $this->visitorWilayahNama = (string) Wilayah::query()
                ->where('kode', $this->visitorWilayahKode)
                ->value('nama');
        }

        $this->step = 3;
    }

    public function messages(): array
    {
        return [
            'visitorName.required' => 'Nama lengkap wajib diisi.',
            'visitorIdentifier.required' => 'NIK/No. Identitas wajib diisi.',
            'visitorPhone.required' => 'No. Telepon wajib diisi.',
            'visitorWilayahKode.required' => 'Pilihan kelurahan/desa wajib diisi.',
            'selectedServiceId.required' => 'Anda harus memilih layanan terlebih dahulu.',
        ];
    }

    public function confirmBooking(CreateQueueTicket $createQueueTicket): void
    {
        $this->validate([
            'selectedServiceId' => ['required', 'integer', 'exists:services,id'],
            'visitorName' => ['required', 'string', 'min:3', 'max:255'],
            'visitorIdentifier' => ['nullable', 'string', 'max:50'],
            'visitorPhone' => ['nullable', 'string', 'max:20'],
            'visitorWilayahKode' => ['required', 'string', $this->wilayahExistsRule()],
        ]);

        $this->ticket = $createQueueTicket->handle([
            'service_id' => $this->selectedServiceId,
            'channel' => 'walk_in_kiosk',
            'service_date' => CarbonImmutable::today(),
            'visitor_name' => $this->visitorName,
            'visitor_identifier' => $this->visitorIdentifier ?: null,
            'visitor_phone' => $this->visitorPhone ?: null,
            'visitor_wilayah_kode' => $this->visitorWilayahKode,
            'notes' => null,
            'created_by' => null,
        ]);

        $this->step = 4; // Step 4 = ticket printed

        $this->barcodeSvg = '';
    }

    public function loadBarcode(): void
    {
        if ($this->ticket === null || $this->barcodeSvg !== '') {
            return;
        }

        $this->barcodeSvg = $this->generateBarcodeSvg($this->ticket->ticket_number);
    }

    public function resetWizard(): void
    {
        $this->step = 1;
        $this->selectedServiceId = null;
        $this->visitorName = '';
        $this->visitorIdentifier = '';
        $this->visitorPhone = '';
        $this->visitorWilayahKode = null;
        $this->visitorWilayahNama = '';
        $this->ticket = null;
        $this->fontSize = 'normal';
        $this->barcodeSvg = '';
        $this->reprintQuery = '';
        $this->reprintTicket = null;
        $this->reprintBarcodeSvg = '';
    }

    /**
     * Generate inline SVG barcode for the given ticket number.
     */
    private function generateBarcodeSvg(string $ticketNumber): string
    {
        $barcode = (new TypeCode128)->getBarcode($ticketNumber);

        $renderer = new SvgRenderer;
        $renderer->setSvgType(SvgRenderer::TYPE_SVG_INLINE);
        $renderer->setForegroundColor([0, 0, 0]);

        return $renderer->render($barcode, 250, 60);
    }

    public function enterReprintMode(): void
    {
        $this->step = 0;
        $this->reprintQuery = '';
        $this->reprintTicket = null;
        $this->reprintBarcodeSvg = '';
    }

    public function exitReprintMode(): void
    {
        $this->step = 1;
        $this->reprintQuery = '';
        $this->reprintTicket = null;
        $this->reprintBarcodeSvg = '';
    }

    public function searchTicketForReprint(): void
    {
        $this->validate([
            'reprintQuery' => ['required', 'string', 'min:3'],
        ]);

        $this->reprintTicket = QueueTicket::query()
            ->with('service')
            ->whereDate('service_date', today())
            ->whereIn('status', [
                QueueStatus::Booked,
                QueueStatus::Waiting,
                QueueStatus::Called,
            ])
            ->where(function ($query) {
                $query->where('visitor_identifier', $this->reprintQuery)
                    ->orWhere('visitor_phone', $this->reprintQuery);
            })
            ->latest()
            ->first();

        if ($this->reprintTicket) {
            $this->reprintBarcodeSvg = $this->generateBarcodeSvg($this->reprintTicket->ticket_number);
        }
    }

    public function render(): View
    {
        return view('livewire.kiosk-booking');
    }

    private function selectedKabupatenKode(): ?string
    {
        return AppSetting::getValue('wilayah.scope.kabupaten_kode');
    }

    private function wilayahExistsRule(): Exists
    {
        $selectedKabupatenKode = $this->selectedKabupatenKode();

        return Rule::exists('wilayah', 'kode')
            ->where(function ($query) use ($selectedKabupatenKode): void {
                $query->whereRaw('LENGTH(kode) = 13');

                if ($selectedKabupatenKode !== null) {
                    $query->where('kode', 'like', "{$selectedKabupatenKode}.%");
                }
            });
    }
}
