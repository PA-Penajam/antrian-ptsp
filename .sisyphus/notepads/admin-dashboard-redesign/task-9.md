2026-03-07
- Mengganti section `Shortcut Manajemen` di `resources/views/components/dashboard/⚡admin-dashboard.blade.php` menjadi grid 5 quick action cards dengan heading `Akses Cepat`.
- Setiap card memakai `wire:navigate`, ikon Flux besar, warna hardcoded Tailwind v4-safe, dan layout responsif `grid gap-4 sm:grid-cols-2 lg:grid-cols-3`.
- Hover state diseragamkan memakai `hover:scale-[1.02] hover:shadow-md transition-all duration-200` agar kartu terasa lebih visual tanpa mengubah URL tujuan.
- Ekspektasi feature test dashboard ikut diperbarui dari `Shortcut Manajemen` menjadi `Akses Cepat` dan diselaraskan dengan label KPI yang memang tampil saat ini.
