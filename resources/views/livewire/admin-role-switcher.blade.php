<div class="px-3 py-2">
    <flux:select wire:model="activeRole" wire:change="switchRole($event.target.value)" variant="listbox" size="sm">
        @foreach (\App\Enums\UserRole::cases() as $role)
            <flux:select.option :value="$role->value">{{ $role->label() }}</flux:select.option>
        @endforeach
    </flux:select>
</div>
