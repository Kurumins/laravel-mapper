<?php

namespace Mapper;

use Mapper\Commands\GeneratorCommand;
use Mapper\Workers\MapperModel;
use Illuminate\Support\ServiceProvider as SP;

class ServiceProvider extends SP
{
    /**
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/Templates/mapper_config.php' => config_path('mapper.php'),
            ], 'mapper');

            $this->commands([
                GeneratorCommand::class,
            ]);
        }
        if (file_exists(config('mapper.path_map'))) {
            MapperModel::loadMap(require(config('mapper.path_map')));
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerModelFactory();
    }

    /**
     * Register Model Factory.
     *
     * @return void
     */
    protected function registerModelFactory()
    {
        if (config('mapper')) {
            $this->app->singleton(Customize::class, function ($app) {
                return new Customize(
                    $app->make('db'), config('mapper')
                );
            });
        }
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [Customize::class];
    }

}