<?php

namespace App\Http\Controllers\Admin;

use App\Enums\QueueStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->with('services')
            ->orderBy('name')
            ->get();

        $services = Service::active()->get();

        return view('pages.admin.users.index', [
            'users' => $users,
            'services' => $services,
            'tab' => $request->query('tab', 'list'),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        if ($validated['role'] === 'officer' && ! empty($validated['service_id'])) {
            $user->services()->sync([$validated['service_id']]);
        }

        return redirect('/admin/users')
            ->with('status', 'User Berhasil Dibuat');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        if ($validated['role'] === 'officer' && ! empty($validated['service_id'])) {
            $user->services()->sync([$validated['service_id']]);
        } else {
            $user->services()->detach();
        }

        return redirect('/admin/users')
            ->with('status', 'User Berhasil Diperbarui');
    }

    public function destroy(User $user): RedirectResponse
    {
        // Block deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        // Block if user has active tickets they created
        $hasActiveTickets = QueueTicket::query()
            ->where('created_by', $user->id)
            ->whereIn('status', [QueueStatus::Waiting, QueueStatus::Called, QueueStatus::Booked])
            ->exists();

        if ($hasActiveTickets) {
            return redirect()->route('admin.users.index')
                ->with('error', 'User tidak dapat dihapus karena memiliki antrian aktif.');
        }

        $user->services()->detach();
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('status', 'User berhasil dihapus.');
    }
}
