<div class="space-y-6">
    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <flux:card class="p-4">
            <flux:heading size="sm">Total Hari Ini</flux:heading>
            <p class="text-3xl font-bold mt-2">{{ $this->todayTotal }}</p>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading size="sm">Sudah Dilayani</flux:heading>
            <p class="text-3xl font-bold mt-2 text-green-600">{{ $this->todayServed }}</p>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading size="sm">Menunggu</flux:heading>
            <p class="text-3xl font-bold mt-2 text-orange-600">{{ $this->todayWaiting }}</p>
        </flux:card>

        <flux:card class="p-4">
            <flux:heading size="sm">Rata-rata Tunggu (menit)</flux:heading>
            <p class="text-3xl font-bold mt-2 text-blue-600">{{ $this->todayAvgWaitMinutes }}</p>
        </flux:card>
    </div>

    {{-- Date Range Filter --}}
    <flux:card class="p-4">
        <div class="flex gap-4 items-end">
            <flux:field>
                <flux:label>Dari Tanggal</flux:label>
                <flux:input type="date" wire:model.live="startDate" />
            </flux:field>

            <flux:field>
                <flux:label>Sampai Tanggal</flux:label>
                <flux:input type="date" wire:model.live="endDate" />
            </flux:field>
        </div>
    </flux:card>

    {{-- Charts placeholder (Task 8 will add actual charts) --}}
    <div id="charts-placeholder" class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Task 8 will add flux:chart components here --}}
    </div>

    {{-- Activity log placeholder (Task 9 will add this) --}}
    <div id="activity-log-placeholder">
        {{-- Task 9 will add activity log here --}}
    </div>
</div>
