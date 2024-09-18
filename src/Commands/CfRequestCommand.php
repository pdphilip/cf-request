<?php

namespace PDPhilip\CfRequest\Commands;

use Illuminate\Console\Command;
use PDPhilip\CfRequest\Cloudflare\Cloudflare;

use function OmniTerm\asyncFunction;
use function OmniTerm\render;

class CfRequestCommand extends Command
{
    public $signature = 'cf-request:headers';

    public $description = 'Uses the Cloudflare API to set the required headers';

    public function handle(): int
    {

        $hasToken = config('cf-request.cloudflare.token');

        if (! $hasToken) {

            render(view('omniterm::status.custom', [
                'status' => 'error',
                'name' => 'Config Error',
                'title' => 'Cloudflare token not found',
                'help' => [
                    'Please set the \'token\' value in the config file, or the CF_API_TOKEN environment variable',
                ],
            ]));

            return self::FAILURE;
        }

        $hasZoneId = config('cf-request.cloudflare.zoneId');

        if (! $hasZoneId) {

            render(view('omniterm::status.custom', [
                'status' => 'error',
                'name' => 'Config Error',
                'title' => 'Cloudflare ZoneId not found',
                'help' => [
                    'Please set the \'zoneId\' value in the config file, or the CF_API_ZONE_ID environment variable',
                ],
            ]));

            return self::FAILURE;
        }

        $async = asyncFunction(function () {
            try {
                $res = Cloudflare::setCfHeaders();
            } catch (\Exception $e) {
                return ['success' => false, 'message' => $e->getMessage()];
            }

            return $res;

        });
        $async->withFailOver(view('omniterm::loaders.spinner', [
            'state' => 'failover',
            'message' => 'Setting Cloudflare Headers',
            'i' => 0,
        ]));
        $result = $async->run(function () use ($async) {
            $async->render(view('omniterm::loaders.spinner', [
                'colors' => ['text-amber-500', 'text-emerald-500', 'text-rose-500', 'text-sky-500'],
                'state' => 'running',
                'message' => 'Setting Cloudflare Headers',
                'i' => $async->getInterval(),
            ]));
        });

        if ($result['success']) {
            $async->render(view('omniterm::loaders.spinner', [
                'state' => 'success',
                'message' => 'Cloudflare Headers Set',
                'details' => $result['message'],
                'i' => $async->getInterval(),
            ]));
        } else {
            $async->render(view('omniterm::loaders.spinner', [
                'state' => 'error',
                'message' => 'Error',
                'details' => $result['message'],
                'i' => $async->getInterval(),
            ]));
        }

        return self::SUCCESS;
    }
}
