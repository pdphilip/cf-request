<?php

namespace PDPhilip\CfRequest\Commands;

use Illuminate\Console\Command;
use OmniTerm\HasOmniTerm;
use PDPhilip\CfRequest\Cloudflare\Cloudflare;

class CfRequestCommand extends Command
{
    use HasOmniTerm;

    public $signature = 'cf-request:headers';

    public $description = 'Uses the Cloudflare API to set the required headers';

    public function handle(): int
    {
        $hasToken = config('cf-request.cloudflare.token');

        if (! $hasToken) {
            $this->omni->statusError(
                'Config Error',
                'Cloudflare token not found',
                ['Please set the \'token\' value in the config file, or the CF_API_TOKEN environment variable'],
            );

            return self::FAILURE;
        }

        $hasZoneId = config('cf-request.cloudflare.zoneId');

        if (! $hasZoneId) {
            $this->omni->statusError(
                'Config Error',
                'Cloudflare ZoneId not found',
                ['Please set the \'zoneId\' value in the config file, or the CF_API_ZONE_ID environment variable'],
            );

            return self::FAILURE;
        }

        $result = $this->omni->task('Setting Cloudflare Headers', function () {
            try {
                $res = Cloudflare::setCfHeaders();
            } catch (\Exception $e) {
                return ['state' => 'error', 'message' => $e->getMessage()];
            }

            return [
                'state' => $res['success'] ? 'success' : 'error',
                'message' => $res['message'],
            ];
        });

        return self::SUCCESS;
    }
}
