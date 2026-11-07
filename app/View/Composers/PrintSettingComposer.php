<?php
namespace App\View\Composers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PrintSettingComposer
{
    public function compose(View $view): void
    {
        $settingsPath = storage_path('app/public/print_settings.json');

        $defaults = [
            'company_name' => config('app.name', 'PT. Devanka Logistik Indonesia'),
            'header_text' => 'Kami siap menangani setiap kebutuhan logistik Anda. Hubungi kami sekarang!',
            'footer_text' => 'PT. Devanka Logistik Indonesia - Simpan dokumen ini sebagai bukti pengiriman',
            'paper_format' => 'A4',
            'company_logo' => null,
        ];

        // Determine document type from view name
        $viewName = $view->getName();
        $docType = 'shipment';
        if (str_contains($viewName, 'invoice'))
            $docType = 'invoice';
        elseif (str_contains($viewName, 'manifest'))
            $docType = 'manifest';
        elseif (str_contains($viewName, 'resi'))
            $docType = 'shipment';

        if (File::exists($settingsPath)) {
            $saved = json_decode(File::get($settingsPath), true);
            if (is_array($saved) && isset($saved[$docType]) && is_array($saved[$docType])) {
                $defaults = array_merge($defaults, $saved[$docType]);
            }
        }

        $logoUrl = null;
        if (!empty($defaults['company_logo'])) {
            $logo = $defaults['company_logo'];
            $logoPath = null;
            if (is_array($logo)) {
                $logoPath = collect($logo)->first();
            } else {
                $logoPath = $logo;
            }
            if ($logoPath) {
                $fullPath = storage_path('app/public/' . $logoPath);
                if (file_exists($fullPath)) {
                    $mime = mime_content_type($fullPath) ?: 'image/png';
                    $base64 = base64_encode(file_get_contents($fullPath));
                    $logoUrl = 'data:' . $mime . ';base64,' . $base64;
                } else {
                    $logoUrl = Storage::url($logoPath);
                }
            }
        }

        // Fallback: use bundled logo file if no uploaded logo
        if (!$logoUrl) {
            $fallback = public_path('images/logo.png');
            if (file_exists($fallback)) {
                $mime = mime_content_type($fallback) ?: 'image/png';
                $logoUrl = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fallback));
            }
        }

        $view->with('printSettings', $defaults);
        $view->with('logoUrl', $logoUrl);
    }
}