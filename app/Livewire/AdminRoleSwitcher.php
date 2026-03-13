<?php

namespace App\Livewire;

use App\Enums\UserRole;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AdminRoleSwitcher extends Component
{
    public string $activeRole = 'admin';

    /** @var array<string, string> Halaman default per role */
    private array $defaultRoutes = [
        'admin' => '/admin/layanan',
        'frontdesk' => '/frontdesk/antrian',
        'officer' => '/workstation',
        'monitor' => '/laporan/antrian',
    ];

    public function mount(): void
    {
        $this->activeRole = session('admin_active_role', 'admin');
    }

    /**
     * Ganti active role dan redirect ke halaman default role tersebut.
     */
    public function switchRole(string $role): mixed
    {
        if (Auth::user()->role !== UserRole::Admin) {
            abort(403);
        }

        if (! UserRole::tryFrom($role)) {
            $this->addError('role', 'Role tidak valid.');

            return null;
        }

        session(['admin_active_role' => $role]);
        $this->activeRole = $role;

        return $this->redirect($this->defaultRoutes[$role]);
    }

    public function render(): mixed
    {
        return view('livewire.admin-role-switcher');
    }
}
