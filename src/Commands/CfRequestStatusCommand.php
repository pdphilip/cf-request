<?php

// Eleganced at 2026-02-20

namespace PDPhilip\CfRequest\Commands;

use Illuminate\Console\Command;
use OmniTerm\HasOmniTerm;
use PDPhilip\CfRequest\Cloudflare\TransformRules;

class CfRequestStatusCommand extends Command
{
    use HasOmniTerm;

    public $signature = 'cf-request:status';

    public $description = 'Check the status of Cloudflare transform rule headers';

    public function handle(): int
    {
        $this->omni->newLine();
        $this->omni->titleBar('CF-Request Status', 'amber');
        $this->omni->newLine();

        if (! $this->hasRequiredConfig()) {
            return self::FAILURE;
        }

        $result = $this->omni->task('Fetching transform rules from Cloudflare', function () {
            try {
                $response = (new TransformRules)->getResponseHeadersRuleset();

                if (! $response->isSuccessFul()) {
                    return ['state' => 'error', 'message' => $response->message];
                }

                return ['state' => 'success', 'message' => 'Fetched', 'data' => $this->parseConfiguredHeaders($response->result)];
            } catch (\Exception $e) {
                return ['state' => 'error', 'message' => $e->getMessage()];
            }
        });

        if (! $result || $result->isError()) {
            $this->omni->statusError('API Error', $result->message ?? 'Failed to connect to Cloudflare');

            return self::FAILURE;
        }

        $this->omni->newLine();
        $this->displayHeaderStatus($result->data);

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

    private function parseConfiguredHeaders(object $result): array
    {
        $configured = [];
        foreach ($result->rules ?? [] as $rule) {
            foreach ($rule->action_parameters->headers ?? [] as $name => $config) {
                $configured[$name] = $config->expression ?? 'static';
            }
        }

        return $configured;
    }

    private function displayHeaderStatus(array $configured): void
    {
        $total = count(TransformRules::allHeaders());
        $found = 0;

        foreach (TransformRules::HEADER_GROUPS as $group => $headers) {
            $this->omni->divider($group);
            $this->omni->tableHeader('Header', 'Status', 'Expression');

            foreach ($headers as $header => $expression) {
                if (isset($configured[$header])) {
                    $this->omni->tableRowSuccess($header, $configured[$header]);
                    $found++;
                } else {
                    $this->omni->tableRowError($header, $expression);
                }
            }
            $this->omni->newLine();
        }

        $this->displayExtras($configured);
        $this->displaySummary($found, $total);
    }

    private function displayExtras(array $configured): void
    {
        $extras = array_diff_key($configured, TransformRules::allHeaders());
        if (! $extras) {
            return;
        }

        $this->omni->divider('Other');
        $this->omni->tableHeader('Header', 'Status', 'Expression');
        foreach ($extras as $header => $expression) {
            $this->omni->tableRowInfo($header, $expression);
        }
        $this->omni->newLine();
    }

    private function displaySummary(int $found, int $total): void
    {
        $missing = $total - $found;

        if ($missing === 0) {
            $this->omni->statusSuccess('All Headers Configured', "{$found}/{$total} transform rule headers are set");

            return;
        }

        if ($found > 0) {
            $this->omni->statusWarning(
                'Headers Missing',
                "{$missing}/{$total} headers not configured",
                ['Run: php artisan cf-request:headers'],
            );

            return;
        }

        $this->omni->statusError(
            'No Headers Found',
            'No CfRequest transform rule headers detected',
            ['Run: php artisan cf-request:headers'],
        );
    }
}
