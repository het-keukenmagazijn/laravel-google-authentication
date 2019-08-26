<?php namespace Keukenmagazijn\LaravelGoogleAuthentication\Providers;

use Illuminate\Support\ServiceProvider;

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
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../config/google_identity.php';
        $migrationPath = __DIR__ . '/../migrations';
        $this->publishes([$configPath => config_path('google_identity.php')], 'km');
        $this->publishes([$migrationPath => database_path('migrations')], 'km');
    }
}
