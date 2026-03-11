<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateWilayahScopeRequest;
use App\Models\AppSetting;
use App\Models\Wilayah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
        $validated = $request->validated();

        AppSetting::setValue('wilayah.scope.kabupaten_kode', $validated['kabupaten_kode']);

        return redirect()->route('admin.wilayah.index')
            ->with('status', 'Setting wilayah berhasil diperbarui.');
    }
}
