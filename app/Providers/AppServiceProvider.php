<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Setting;
use App\Observers\LoggableObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(LoggableObserver::class);
        Role::observe(LoggableObserver::class);
        Permission::observe(LoggableObserver::class);
        Setting::observe(LoggableObserver::class);
    }
}
