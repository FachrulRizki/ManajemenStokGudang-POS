<?php

namespace App\Providers;

use App\Models\AppSetting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Gunakan custom pagination view
        Paginator::defaultView('vendor.pagination.simple');
        Paginator::defaultSimpleView('vendor.pagination.simple');

        // Share app settings ke semua view (cached 1 jam)
        try {
            View::share('appName', AppSetting::get('app_name', config('app.name')));
            View::share('appTheme', AppSetting::get('app_theme', 'indigo'));
        } catch (\Exception $e) {
            // DB belum siap (saat migration awal)
            View::share('appName', config('app.name'));
            View::share('appTheme', 'indigo');
        }
    }
}
