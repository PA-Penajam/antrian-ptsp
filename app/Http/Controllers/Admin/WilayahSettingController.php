<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateWilayahScopeRequest;
use App\Models\AppSetting;
use App\Models\Wilayah;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class WilayahSettingController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $selectedKabupatenKode = AppSetting::getValue('wilayah.scope.kabupaten_kode');
        $selectedKabupaten = null;

        if ($selectedKabupatenKode !== null) {
            $selectedKabupaten = Wilayah::query()->find($selectedKabupatenKode);
        }

        $kabupatenList = Wilayah::query()
            ->whereRaw('LENGTH(kode) = 5')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery->where('nama', 'like', "%{$search}%")
                        ->orWhere('kode', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama')
            ->paginate(25)
            ->withQueryString();

        return view('pages.admin.wilayah.index', [
            'kabupatenList' => $kabupatenList,
            'selectedKabupaten' => $selectedKabupaten,
            'selectedKabupatenKode' => $selectedKabupatenKode,
        ]);
    }

    public function update(UpdateWilayahScopeRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();

            AppSetting::setValue('wilayah.scope.kabupaten_kode', $validated['kabupaten_kode']);

            return redirect()->route('admin.wilayah.index')
                ->with('status', 'Setting wilayah berhasil diperbarui.');
        } catch (QueryException $e) {
            Log::warning('[Admin][Wilayah] Gagal memperbarui setting (constraint)', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'user_id' => auth()->id(),
            ]);

            return back()->withInput()
                ->with('error', 'Gagal menyimpan setting wilayah. Coba lagi.');
        } catch (Throwable $e) {
            Log::error('[Admin][Wilayah] Gagal memperbarui setting wilayah', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'input' => $request->except(['_token', '_method']),
            ]);

            return back()->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui setting wilayah. Periksa koneksi dan coba lagi.');
        }
    }
}
