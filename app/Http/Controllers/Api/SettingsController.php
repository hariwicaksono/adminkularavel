<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        return Setting::all();
    }

    public function update(Request $request, $key)
    {
        if ($key === 'site_logo') {
            // Validasi file logo (wajib)
            $request->validate([
                'logo' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            ]);

            // Ambil logo lama
            $oldLogo = Setting::where('key', 'site_logo')->value('value');

            // Upload logo baru
            $path = $request->file('logo')->store('logos', 'public');
            $newPath = '/storage/' . $path;

            // Simpan ke DB
            Setting::updateOrCreate(['key' => 'site_logo'], ['value' => $newPath]);

            // Hapus logo lama jika ada
            if ($oldLogo && Storage::disk('public')->exists(str_replace('/storage/', '', $oldLogo))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $oldLogo));
            }

            return response()->json([
                'message' => 'Logo updated',
                'value' => $newPath,
            ]);
        }

        // Validasi untuk field value biasa
        $validated = $request->validate([
            'value' => 'required',
        ]);

        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $validated['value']]
        );

        return response()->json([
            'message' => 'Setting updated',
            'key' => $key,
            'value' => $validated['value'],
        ]);
    }
}
