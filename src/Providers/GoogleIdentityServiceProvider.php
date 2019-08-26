<?php namespace Keukenmagazijn\LaravelGoogleAuthentication\Providers;

use Illuminate\Support\ServiceProvider;
use Keukenmagazijn\LaravelGoogleAuthentication\Facades\GoogleIdentityFacade;

class GoogleIdentityServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     * @throws \Exception
     */
    public function register()
    {
        $configPath = __DIR__ . '/../config/google_identity.php';
        $this->mergeConfigFrom($configPath, 'google_identity');
//        $this->app->alias(GoogleIdentityFacade::class, 'googleidentity');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../config/google_identity.php';
        $this->publishes([$configPath => config_path('google_identity.php')], 'km');
    }
}
