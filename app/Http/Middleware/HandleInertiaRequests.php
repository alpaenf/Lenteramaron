<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
            ],
            'app_settings' => fn () => [
                'school_name' => Setting::getByKey('school_name', 'SD Negeri 02 Maron'),
                'library_name' => Setting::getByKey('library_name', 'LENTERA MARON'),
                'logo' => Setting::getByKey('logo'),
                'gmaps_url' => Setting::getByKey('gmaps_url'),
                'email' => Setting::getByKey('email'),
                'phone' => Setting::getByKey('phone'),
                'address' => Setting::getByKey('address'),
            ],
        ];
    }
}
