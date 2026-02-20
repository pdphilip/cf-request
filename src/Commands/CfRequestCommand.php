<?php

// Eleganced at 2026-02-20

namespace PDPhilip\CfRequest\Commands;

use Illuminate\Console\Command;
use OmniTerm\HasOmniTerm;
use PDPhilip\CfRequest\Cloudflare\TransformRules;

class CfRequestCommand extends Command
{
    use HasOmniTerm;

    public $signature = 'cf-request:headers';

    public $description = 'Create or update Cloudflare transform rule headers';

    public function handle(): int
    {
        $this->omni->newLine();
        $this->omni->titleBar('CF-Request Headers', 'amber');
        $this->omni->newLine();

        if (! $this->hasRequiredConfig()) {
            return self::FAILURE;
        }

        $this->showHeadersPreview();

        $result = $this->omni->task('Configuring Cloudflare transform rules', function () {
            try {
                $response = (new TransformRules)->setLaravelHeaders();

                return [
                    'state' => ($response['success'] ?? false) ? 'success' : 'error',
                    'message' => $response['message'] ?? 'Unknown error',
                    'data' => $response,
                ];
            } catch (\Exception $e) {
                return ['state' => 'error', 'message' => $e->getMessage()];
            }
        });

        if (! $result || ! ($result->data['success'] ?? false)) {
            $this->omni->newLine();
            $this->omni->statusError('Failed', $result->data['message'] ?? $result->message ?? 'Unknown error');

            return self::FAILURE;
        }

        $action = $result->data['action'] === 'created' ? 'Created' : 'Updated';
        $count = count(TransformRules::allHeaders());

        $this->omni->newLine();
        $this->omni->statusSuccess(
            "Transform Rule {$action}",
            "{$count} headers configured on Cloudflare",
            ['Run: php artisan cf-request:status to verify'],
        );

        return self::SUCCESS;
    }

    // ----------------------------------------------------------------------
    // Internal
    // ----------------------------------------------------------------------

    private function hasRequiredConfig(): bool
    {
        if (! config('cf-request.cloudflare.token')) {
            $this->omni->statusError('Config Error', 'Cloudflare API token not found', ['Set CF_API_TOKEN in your .env file']);

            return false;
        }

        if (! config('cf-request.cloudflare.zoneId')) {
            $this->omni->statusError('Config Error', 'Cloudflare Zone ID not found', ['Set CF_API_ZONE_ID in your .env file']);

            return false;
        }

        return true;
    }

    private function showHeadersPreview(): void
    {
        $count = count(TransformRules::allHeaders());
        $this->omni->divider("Headers ({$count})");
        $this->omni->tableHeader('Header', 'Value', 'Expression');

        foreach (TransformRules::HEADER_GROUPS as $headers) {
            foreach ($headers as $name => $expression) {
                $this->omni->tableRow($name, $expression);
            }
        }

        $this->omni->newLine();
    }
}
