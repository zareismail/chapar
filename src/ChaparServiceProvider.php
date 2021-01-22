<?php

namespace Zareismail\Chapar;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Nova\Nova as LaravelNova; 

class ChaparServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Models\ChaparLetter::class => Policies\Letter::class, 
    ]; 

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    { 
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations'); 
        $this->registerPolicies();
        LaravelNova::resources([
            Nova\Letter::class,
        ]);  
    } 
 
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    /**
     * Get the events that trigger this service provider to register.
     *
     * @return array
     */
    public function when()
    {
        return [
            \Laravel\Nova\Events\ServingNova::class,
            \Illuminate\Console\Events\ArtisanStarting::class,
        ];
    }
}
