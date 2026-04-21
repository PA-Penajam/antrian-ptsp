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
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

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
        try {
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
        } catch (Throwable $e) {
            Log::error('[Admin][User] Gagal membuat user', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'input' => $request->except(['_token', 'password']),
            ]);

            return redirect('/admin/users')
                ->with('error', 'Terjadi kesalahan saat membuat user. Silakan coba lagi.');
        }
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        try {
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
        } catch (Throwable $e) {
            Log::error('[Admin][User] Gagal memperbarui user', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'target_user_id' => $user->id,
                'input' => $request->except(['_token', '_method', 'password']),
            ]);

            return redirect('/admin/users')
                ->with('error', 'Terjadi kesalahan saat memperbarui user. Silakan coba lagi.');
        }
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

        try {
            $user->services()->detach();
            $user->delete();

            return redirect()->route('admin.users.index')
                ->with('status', 'User berhasil dihapus.');
        } catch (Throwable $e) {
            Log::error('[Admin][User] Gagal menghapus user', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'target_user_id' => $user->id,
            ]);

            return redirect()->route('admin.users.index')
                ->with('error', 'Terjadi kesalahan saat menghapus user. Silakan coba lagi.');
        }
    }
}
