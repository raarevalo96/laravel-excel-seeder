<?php

namespace bfinlay\SpreadsheetSeeder;

use bfinlay\SpreadsheetSeeder\Console\SeedCommand;
use Illuminate\Support\ServiceProvider;

class SpreadsheetSeederServiceProvider extends ServiceProvider
{
    /**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
    protected $defer = false;

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->singleton(SpreadsheetSeederSettings::class, function ($app) {
            return new SpreadsheetSeederSettings();
        });

//        $this->app->singleton('command.seed', function ($app) {
//            return new SeedCommand($app['db']);
//        });

        $this->app->singleton(SeedCommand::class, function ($app) {
            return new SeedCommand($app['db']);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->commands([
//            'command.seed',
            SeedCommand::class,
        ]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }
}
