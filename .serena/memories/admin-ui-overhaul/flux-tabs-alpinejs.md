# Flux Tabs with Alpine.js - Implementation Notes

## Problem
When using `<flux:tab>` with Alpine.js binding like `:current="tab === 'list'"`, Blade interprets `tab` as a PHP constant instead of an Alpine.js variable, causing:
```
Error: Undefined constant "tab"
```

## Root Cause
The `:current` prop on Flux components is a Blade binding that compiles to PHP. When Blade sees `:current="tab === 'list'"`, it tries to evaluate `tab` as a PHP variable/constant, not an Alpine.js variable.

## Solution
For non-Livewire pages using Alpine.js for tab state:

1. **Use custom buttons instead of `<flux:tab>` components:**
```blade
<div x-data="{ tab: 'list' }">
    <button 
        x-on:click="tab = 'list'"
        :class="tab === 'list' ? 'active-classes' : 'inactive-classes'"
    >
        Tab Label
    </button>
    <!-- content panels with x-show -->
    <div x-show="tab === 'list'">...</div>
</div>
```

2. **Don't use `:current` prop with Alpine.js variables** - it's designed for Livewire components where `$tab` would be a PHP property.

## Working Pattern
```blade
<flux:card>
    <div class="flex gap-1 border-b border-zinc-200 pb-1">
        <button
            type="button"
            x-on:click="tab = 'list'"
            :class="tab === 'list' ? 'bg-zinc-100 text-zinc-900' : 'text-zinc-600'"
            class="rounded-md px-3 py-1.5 text-sm font-medium"
        >
            Tab 1
        </button>
        <!-- more tabs -->
    </div>
</flux:card>

<div x-show="tab === 'list'" x-cloak>
    <!-- tab content -->
</div>
```

## Key Learnings
- Flux UI components use Blade bindings (`:prop`) which compile to PHP
- Alpine.js variables should not be used in Blade prop bindings
- Use plain HTML elements with Alpine directives for client-side state
- `x-cloak` prevents flash of unstyled content before Alpine initializes
