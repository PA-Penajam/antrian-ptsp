@php
    $fluxManifestPath = \Flux\Flux::pro()
        ? base_path('vendor/livewire/flux-pro/dist/manifest.json')
        : base_path('vendor/livewire/flux/dist/manifest.json');

    $fluxVersion = null;

    if (is_file($fluxManifestPath)) {
        $fluxManifest = json_decode((string) file_get_contents($fluxManifestPath), true);
        $fluxVersion = $fluxManifest['/flux.js'] ?? null;
    }

    $fluxScriptUrl = route('flux.script');

    if ($fluxVersion !== null && $fluxVersion !== '') {
        $fluxScriptUrl .= '?id='.$fluxVersion;
    }
@endphp
<script src="{{ $fluxScriptUrl }}" data-navigate-once></script>
