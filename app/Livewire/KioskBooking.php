<?php

namespace App\Livewire;

use App\Actions\Queue\CreateQueueTicket;
use App\Models\QueueTicket;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
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

    public ?QueueTicket $ticket = null;

    public string $fontSize = 'normal';

    public string $barcodeSvg = '';

    #[Computed]
    public function services(): Collection
    {
        return Service::query()
            ->where('is_active', true)
            ->where('walk_in_enabled', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function selectedService(): ?Service
    {
        if ($this->selectedServiceId === null) {
            return null;
        }

        return Service::query()->find($this->selectedServiceId);
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

    public function submitData(): void
    {
        $this->validate([
            'visitorName' => ['required', 'string', 'min:3', 'max:255'],
            'visitorIdentifier' => ['nullable', 'string', 'max:50'],
            'visitorPhone' => ['nullable', 'string', 'max:20'],
        ]);

        $this->step = 3;
    }

    public function confirmBooking(CreateQueueTicket $createQueueTicket): void
    {
        $this->validate([
            'selectedServiceId' => ['required', 'integer', 'exists:services,id'],
            'visitorName' => ['required', 'string', 'min:3', 'max:255'],
        ]);

        $this->ticket = $createQueueTicket->handle([
            'service_id' => $this->selectedServiceId,
            'channel' => 'walk_in_kiosk',
            'service_date' => CarbonImmutable::today(),
            'visitor_name' => $this->visitorName,
            'visitor_identifier' => $this->visitorIdentifier ?: null,
            'visitor_phone' => $this->visitorPhone ?: null,
            'notes' => null,
            'created_by' => null,
        ]);

        $this->step = 4; // Step 4 = ticket printed

        $this->barcodeSvg = $this->generateBarcodeSvg($this->ticket->ticket_number);
    }

    public function resetWizard(): void
    {
        $this->step = 1;
        $this->selectedServiceId = null;
        $this->visitorName = '';
        $this->visitorIdentifier = '';
        $this->visitorPhone = '';
        $this->ticket = null;
        $this->fontSize = 'normal';
        $this->barcodeSvg = '';
    }

    /**
     * Generate inline SVG barcode for the given ticket number.
     */
    private function generateBarcodeSvg(string $ticketNumber): string
    {
        $barcode = (new TypeCode128)->getBarcode($ticketNumber);

        $renderer = new SvgRenderer;
        $renderer->setSvgType(SvgRenderer::TYPE_SVG_INLINE);
        $renderer->setForegroundColor([255, 255, 255]);

        return $renderer->render($barcode, 250, 60);
    }

    public function render(): View
    {
        return view('livewire.kiosk-booking');
    }
}
