<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class PrintSettingController extends Controller
{
    private function getSettingsPath()
    {
        return storage_path('app/public/print_settings.json');
    }

    private function loadAllSettings()
    {
        $defaults = [
            'invoice' => [
                'company_name' => config('app.name', 'Logistik Tracking System'),
                'header_text' => 'Dokumen ini dicetak secara otomatis',
                'footer_text' => 'Logistik Tracking System — Confidential',
                'paper_format' => 'A4',
                'company_logo' => null,
            ],
            'manifest' => [
                'company_name' => config('app.name', 'Logistik Tracking System'),
                'header_text' => 'Dokumen ini dicetak secara otomatis',
                'footer_text' => 'Logistik Tracking System — Confidential',
                'paper_format' => 'A4',
                'company_logo' => null,
            ],
            'shipment' => [
                'company_name' => config('app.name', 'Logistik Tracking System'),
                'header_text' => 'Dokumen ini dicetak secara otomatis',
                'footer_text' => 'Logistik Tracking System — Confidential',
                'paper_format' => 'A4',
                'company_logo' => null,
            ],
        ];

        $path = $this->getSettingsPath();
        if (File::exists($path)) {
            $saved = json_decode(File::get($path), true);
            if (is_array($saved)) {
                foreach ($saved as $type => $data) {
                    if (isset($defaults[$type]) && is_array($data)) {
                        $defaults[$type] = array_merge($defaults[$type], $data);
                    }
                }
            }
        }
        return $defaults;
    }

    public function index()
    {
        $allSettings = $this->loadAllSettings();

        $firstShipment = \App\Models\Shipment::withoutGlobalScopes()->first();
        $firstManifest = \App\Models\Manifest::withoutGlobalScopes()->first();
        $firstInvoice = \App\Models\Invoice::withoutGlobalScopes()->first();

        return view('template-management', compact('allSettings', 'firstShipment', 'firstManifest', 'firstInvoice'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'doc_type' => 'required|in:invoice,manifest,shipment',
            'company_name' => 'required|string|max:255',
            'header_text' => 'nullable|string',
            'footer_text' => 'nullable|string',
            'paper_format' => 'required|in:A4,Letter,thermal,dotmatrix-half',
            'company_logo' => 'nullable|image|max:2048',
        ]);

        $allSettings = $this->loadAllSettings();
        $type = $request->doc_type;

        $allSettings[$type] = [
            'company_name' => $request->company_name,
            'header_text' => $request->header_text,
            'footer_text' => $request->footer_text,
            'paper_format' => $request->paper_format,
            'company_logo' => $allSettings[$type]['company_logo'] ?? null,
        ];

        if ($request->hasFile('company_logo')) {
            $allSettings[$type]['company_logo'] = $request->file('company_logo')->store('logos', 'public');
        }

        $path = $this->getSettingsPath();
        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($allSettings, JSON_PRETTY_PRINT));

        return back()->with('success', 'Pengaturan ' . ucfirst($type) . ' tersimpan');
    }
}