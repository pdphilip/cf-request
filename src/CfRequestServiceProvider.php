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
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('cf-request')
            ->hasConfigFile()
            ->hasViews('cf-request')
            ->hasMigration('create_cf_request_table')
            ->hasCommand(CfRequestCommand::class)
            ->publishesServiceProvider('CfRequestServiceProvider')
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->setName('cf-request:install')
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->copyAndRegisterServiceProviderInApp()
                    ->askToStarRepoOnGitHub('pdphilip/cf-request');
            });
    }

    public function packageRegistered()
    {
        Route::get('/cf-request/status', [CloudflareStatusController::class, 'index'])->name('cf-request.status');
    }
}
