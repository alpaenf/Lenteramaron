<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Traits\CompressesImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    use CompressesImage;

    public function index(): Response
    {
        $settings = [
            'school_name' => Setting::getByKey('school_name', 'SD Negeri 02 Maron'),
            'library_name' => Setting::getByKey('library_name', 'LENTERA MARON'),
            'school_address' => Setting::getByKey('school_address'),
            'school_email' => Setting::getByKey('school_email'),
            'school_phone' => Setting::getByKey('school_phone'),
            'headmaster_name' => Setting::getByKey('headmaster_name'),
            'librarian_name' => Setting::getByKey('librarian_name'),
            'vision' => Setting::getByKey('vision'),
            'mission' => Setting::getByKey('mission'),
            'gmaps_embed_url' => Setting::getByKey('gmaps_embed_url'),
            'spreadsheet_url' => Setting::getByKey('spreadsheet_url'),
            'logo_path' => Setting::getByKey('logo_path'),
            'hero_banner_path' => Setting::getByKey('hero_banner_path'),
            'profile_photo_1' => Setting::getByKey('profile_photo_1'),
            'profile_photo_2' => Setting::getByKey('profile_photo_2'),
            'profile_photo_3' => Setting::getByKey('profile_photo_3'),
            'profile_photo_4' => Setting::getByKey('profile_photo_4'),
        ];

        return Inertia::render('Settings/Index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'school_name' => 'required|string|max:255',
            'library_name' => 'required|string|max:255',
            'school_address' => 'nullable|string',
            'school_email' => 'nullable|email|max:255',
            'school_phone' => 'nullable|string|max:100',
            'headmaster_name' => 'nullable|string|max:255',
            'librarian_name' => 'nullable|string|max:255',
            'vision' => 'nullable|string',
            'mission' => 'nullable|string',
            'gmaps_embed_url' => 'nullable|string',
            'spreadsheet_url' => 'nullable|url',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'hero_banner' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'profile_photo_1' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'profile_photo_2' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'profile_photo_3' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'profile_photo_4' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        foreach (['school_name', 'library_name', 'school_address', 'school_email', 'school_phone', 'headmaster_name', 'librarian_name', 'vision', 'mission', 'gmaps_embed_url', 'spreadsheet_url'] as $key) {
            if (array_key_exists($key, $validated)) {
                Setting::setByKey($key, $validated[$key]);
            }
        }

        if ($request->hasFile('logo')) {
            $path = $this->compressAndSaveImage($request->file('logo'), 'settings');
            Setting::setByKey('logo_path', $path);
        }

        if ($request->hasFile('hero_banner')) {
            $path = $this->compressAndSaveImage($request->file('hero_banner'), 'settings');
            Setting::setByKey('hero_banner_path', $path);
        }

        for ($i = 1; $i <= 4; $i++) {
            $keyName = "profile_photo_{$i}";
            if ($request->hasFile($keyName)) {
                $path = $this->compressAndSaveImage($request->file($keyName), 'settings');
                Setting::setByKey($keyName, $path);
            }
        }

        return redirect()->route('settings.index')->with('success', 'Pengaturan perpustakaan berhasil diperbarui.');
    }
}
