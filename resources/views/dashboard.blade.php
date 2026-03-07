<x-layouts::app :title="__('Dashboard')">
    <div class="space-y-6">
        <div>
            <flux:heading size="xl" level="1">Ringkasan PTSP Hari Ini</flux:heading>
        </div>

        <div class="grid gap-6 md:grid-cols-5">
            <flux:card>
                <flux:heading size="lg">Menunggu</flux:heading>
                <flux:text class="text-3xl font-semibold mt-2 text-zinc-900 dark:text-zinc-100">{{ $summary['waiting'] ?? 0 }}</flux:text>
            </flux:card>
            <flux:card>
                <flux:heading size="lg">Dipanggil</flux:heading>
                <flux:text class="text-3xl font-semibold mt-2 text-zinc-900 dark:text-zinc-100">{{ $summary['called'] ?? 0 }}</flux:text>
            </flux:card>
            <flux:card>
                <flux:heading size="lg">Selesai</flux:heading>
                <flux:text class="text-3xl font-semibold mt-2 text-zinc-900 dark:text-zinc-100">{{ $summary['completed'] ?? 0 }}</flux:text>
            </flux:card>
            <flux:card>
                <flux:heading size="lg">Batal</flux:heading>
                <flux:text class="text-3xl font-semibold mt-2 text-zinc-900 dark:text-zinc-100">{{ $summary['cancelled'] ?? 0 }}</flux:text>
            </flux:card>
            <flux:card>
                <flux:heading size="lg">Total</flux:heading>
                <flux:text class="text-3xl font-semibold mt-2 text-zinc-900 dark:text-zinc-100">{{ $summary['total'] ?? 0 }}</flux:text>
            </flux:card>
        </div>
    </div>
</x-layouts::app>
