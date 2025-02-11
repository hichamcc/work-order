<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use LivewireUI\Modal\Modal;

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
    }
}
