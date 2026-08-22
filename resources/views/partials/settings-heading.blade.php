<div class="relative mb-6 w-full space-y-1 animate-fade-in-up motion-reduce:animate-none">
    <flux:breadcrumbs class="mb-1">
        <flux:breadcrumbs.item :href="route('dashboard')" icon="home" aria-label="Beranda" />
        <flux:breadcrumbs.item>{{ __('Pengaturan') }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <flux:heading size="xl" level="1" class="font-extrabold tracking-tight text-zinc-900 dark:text-white">{{ __('Pengaturan Akun') }}</flux:heading>
            <flux:subheading class="text-zinc-600 dark:text-zinc-400">{{ __('Kelola informasi profil, keamanan kredensial, dan preferensi tampilan akun Anda.') }}</flux:subheading>
        </div>
    </div>
    <div class="pt-3">
        <flux:separator variant="subtle" />
    </div>
</div>
