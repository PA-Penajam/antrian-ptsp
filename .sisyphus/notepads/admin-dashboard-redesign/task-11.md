2026-03-07
- Menambahkan `resources/views/components/dashboard/notification-panel.blade.php` sebagai Livewire single-file component memakai `flux:dropdown` + `flux:popover`.
- Sumber data memakai `queue_activities` dengan eager load `queueTicket` agar tiket bisa ditampilkan tanpa N+1 query.
- Badge unread dihitung dari aktivitas 5 menit terakhir dan dibersihkan in-memory saat dropdown dibuka lewat `wire:model="isOpen"`.
