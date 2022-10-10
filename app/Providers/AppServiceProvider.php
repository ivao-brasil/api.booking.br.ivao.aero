<?php

namespace App\Providers;

use App\Contracts\CSVFileServiceInterface;
use App\Services\CSVFileService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() === 'local') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
            $this->app->register(\Flipbox\LumenGenerator\LumenGeneratorServiceProvider::class);
        }

        $this->app->bind(CSVFileServiceInterface::class, CSVFileService::class);
    }
}
