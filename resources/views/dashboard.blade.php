<x-layouts::app :title="__('Dashboard')">
    <div class="space-y-4">
        <div>
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Ringkasan PTSP Hari Ini</h1>
        </div>

        <div class="grid gap-4 md:grid-cols-5">
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Menunggu</p>
                <p class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $summary['waiting'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Dipanggil</p>
                <p class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $summary['called'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Selesai</p>
                <p class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $summary['completed'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Batal</p>
                <p class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $summary['cancelled'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Total</p>
                <p class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $summary['total'] ?? 0 }}</p>
            </div>
        </div>
    </div>
</x-layouts::app>
