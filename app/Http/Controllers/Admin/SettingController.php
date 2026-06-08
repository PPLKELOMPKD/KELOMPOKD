<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function index(): Response
    {
        // Seed default settings if they don't exist
        $defaults = [
            ['key' => 'app_name', 'value' => 'SIKARA', 'type' => 'string', 'group' => 'general', 'label' => 'Nama Aplikasi'],
            ['key' => 'app_description', 'value' => 'Sistem Informasi Kampus Merdeka', 'type' => 'string', 'group' => 'general', 'label' => 'Deskripsi Aplikasi'],
            ['key' => 'contact_email', 'value' => 'admin@sikara.ac.id', 'type' => 'string', 'group' => 'general', 'label' => 'Email Kontak'],
            ['key' => 'footer_text', 'value' => '© 2026 SIKARA. Hak cipta dilindungi undang-undang.', 'type' => 'string', 'group' => 'tampilan', 'label' => 'Teks Footer'],
            ['key' => 'social_instagram', 'value' => 'https://instagram.com/sikara', 'type' => 'string', 'group' => 'tampilan', 'label' => 'Link Instagram'],
            ['key' => 'maintenance_mode', 'value' => 'false', 'type' => 'boolean', 'group' => 'system', 'label' => 'Mode Pemeliharaan (Maintenance)'],
            ['key' => 'session_lifetime', 'value' => '120', 'type' => 'integer', 'group' => 'system', 'label' => 'Durasi Sesi (Menit)'],
            ['key' => 'registration_enabled', 'value' => 'true', 'type' => 'boolean', 'group' => 'system', 'label' => 'Buka Pendaftaran Akun Baru'],
        ];

        foreach ($defaults as $default) {
            Setting::firstOrCreate(
                ['key' => $default['key']],
                $default
            );
        }

        $settings = Setting::orderBy('group')->orderBy('label')->get()->groupBy('group');

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'settings' => 'required|array',
            'settings.*.id' => 'required|exists:settings,id',
            'settings.*.value' => 'nullable',
        ]);

        foreach ($data['settings'] as $settingData) {
            $setting = Setting::find($settingData['id']);
            
            // if boolean, handle empty string as false, 'true' as true etc
            if ($setting->type === 'boolean') {
                $setting->value = in_array($settingData['value'], [true, 'true', 1, '1'], true) ? 'true' : 'false';
            } elseif ($setting->type === 'integer') {
                $val = (int) $settingData['value'];
                $setting->value = (string) ($val > 0 ? $val : 120); // fallback to 120 minutes
            } else {
                $setting->value = (string) $settingData['value'];
            }
            
            if ($setting->isDirty('value')) {
                $oldValue = $setting->getOriginal('value');
                $newValue = $setting->value;
                $setting->save();
                
                ActivityLogger::log(
                    'Konfigurasi Sistem',
                    "Admin mengubah '{$setting->label}' dari '{$oldValue}' menjadi '{$newValue}'",
                    'admin'
                );
            }
        }

        Cache::forget('global_settings');

        return redirect()->back()->with('success', 'Settings saved successfully.');
    }
}
