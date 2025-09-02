<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;


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
        Activity::saving(function (Activity $activity) {
            // Safely merge request context into properties
            $current = $activity->properties ?? collect();

            $activity->properties = $current->merge([
                'ip'         => request()->ip(),
                'device'     => request()->userAgent(),
                'role'       => auth()->check() ? auth()->user()->getRoleNames()->first() : null,
                'route_name' => optional(request()->route())->getName(),
                'method'     => request()->method(),
            ]);
        });
    }

   
}
