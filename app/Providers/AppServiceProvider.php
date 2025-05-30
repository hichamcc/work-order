<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use LivewireUI\Modal\Modal;
use Carbon\Carbon;

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
        // Register the modal function on the Livewire facade
        Livewire::listen('modal', function ($component, $arguments = []) {
            return Modal::dispatch($component, $arguments);
        });


        Carbon::macro('inApplicationTimezone',function(){
            return $this->tz(config('app.timezone_display'));
         });
    }
}
