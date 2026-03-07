<x-layouts::app :title="__('Dashboard')">
    <div class="space-y-6">
        <div>
            <flux:heading size="xl" level="1">Ringkasan PTSP Hari Ini</flux:heading>
        </div>

        <div class="grid gap-6 md:grid-cols-5">
            <flux:card>
                <flux:subheading>Menunggu</flux:subheading>
                <flux:heading size="3xl" class="mt-2">{{ $summary['waiting'] ?? 0 }}</flux:heading>
            </flux:card>
            <flux:card>
                <flux:subheading>Dipanggil</flux:subheading>
                <flux:heading size="3xl" class="mt-2">{{ $summary['called'] ?? 0 }}</flux:heading>
            </flux:card>
            <flux:card>
                <flux:subheading>Selesai</flux:subheading>
                <flux:heading size="3xl" class="mt-2">{{ $summary['completed'] ?? 0 }}</flux:heading>
            </flux:card>
            <flux:card>
                <flux:subheading>Batal</flux:subheading>
                <flux:heading size="3xl" class="mt-2">{{ $summary['cancelled'] ?? 0 }}</flux:heading>
            </flux:card>
            <flux:card>
                <flux:subheading>Total</flux:subheading>
                <flux:heading size="3xl" class="mt-2">{{ $summary['total'] ?? 0 }}</flux:heading>
            </flux:card>
        </div>
    </div>
</x-layouts::app>
