<?php

namespace PDPhilip\CfRequest;

use Illuminate\Support\Facades\Route;
use PDPhilip\CfRequest\Commands\CfRequestCommand;
use PDPhilip\CfRequest\Http\Controllers\CloudflareStatusController;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CfRequestServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('cf-request')
            ->hasConfigFile()
            ->hasCommand(CfRequestCommand::class)
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->setName('cf-request:install')
                    ->publishConfigFile()
                    ->copyAndRegisterServiceProviderInApp()
                    ->askToStarRepoOnGitHub('pdphilip/cf-request');
            });
    }

    public function packageBooted(): void
    {
        $this->app->bind(CfRequest::class, function ($app) {
            return new CfRequest($app['request']);
        });
    }

    public function packageRegistered(): void
    {
        Route::get('/cf-request/status', [CloudflareStatusController::class, 'index'])->name('cf-request.status');
        Route::get('/cf-request/status-request', [CloudflareStatusController::class, 'indexAsRequest'])->name('cf-request.status-request');
    }
}
