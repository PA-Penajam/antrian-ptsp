<x-layouts::app :title="__('Dashboard')">
    @php
        $role = $activeRole ?? auth()->user()?->role;
        $role = $role instanceof \BackedEnum ? $role->value : $role;
    @endphp

    @if (in_array($role, ['officer', 'frontdesk'], true))
        <x-dashboard.petugas-dashboard />
    @elseif ($role === 'monitor')
        <x-dashboard.monitor-dashboard />
    @elseif ($role === 'admin')
        <x-dashboard.admin-dashboard />
    @else
        <livewire:dashboard-stats />
    @endif
</x-layouts::app>
