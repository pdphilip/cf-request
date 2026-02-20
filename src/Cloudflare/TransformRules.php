<?php

// Eleganced at 2026-02-20

namespace PDPhilip\CfRequest\Cloudflare;

// https://developers.cloudflare.com/api/operations/createZoneRuleset

class TransformRules
{
    public const HEADER_GROUPS = [
        'Geolocation' => [
            'X-COUNTRY' => 'ip.src.country',
            'X-CITY' => 'ip.src.city',
            'X-REGION' => 'ip.src.region',
            'X-CONTINENT' => 'ip.src.continent',
            'X-POSTAL-CODE' => 'ip.src.postal_code',
            'X-LAT' => 'ip.src.lat',
            'X-LON' => 'ip.src.lon',
            'X-TIMEZONE' => 'ip.src.timezone.name',
        ],
        'Request' => [
            'X-IP' => 'ip.src',
            'X-ASN' => 'ip.src.asnum',
            'X-AGENT' => 'http.user_agent',
            'X-REFERER' => 'http.referer',
            'X-LANG' => 'http.request.accepted_languages[0]',
        ],
        'Bot Detection' => [
            'X-BOT-CAT' => 'cf.verified_bot_category',
        ],
    ];

    private string $zoneId;

    private ?string $rulesetId = null;

    public function __construct()
    {
        $this->zoneId = config('cf-request.cloudflare.zoneId') ?? '';
    }

    // ----------------------------------------------------------------------
    // Public API
    // ----------------------------------------------------------------------

    public static function allHeaders(): array
    {
        return array_merge(...array_values(self::HEADER_GROUPS));
    }

    public function checkAuth(): ApiResponse
    {
        return ApiResponse::get('/zones/'.$this->zoneId.'/rulesets');
    }

    public function getResponseHeadersRuleset(): ApiResponse
    {
        return ApiResponse::get('/zones/'.$this->zoneId.'/rulesets/phases/http_request_late_transform/entrypoint');
    }

    public function findLaravelRule(): ?object
    {
        $response = $this->getResponseHeadersRuleset();

        foreach ($response->result->rules ?? [] as $rule) {
            if ($rule->description === 'Laravel Headers') {
                return $rule;
            }
        }

        return null;
    }

    /**
     * Create or update the "Laravel Headers" transform rule.
     *
     * Returns: ['success' => bool, 'action' => 'created'|'updated'|'error', 'message' => string]
     */
    public function setLaravelHeaders(): array
    {
        // 1. Verify API credentials
        $auth = $this->checkAuth();
        if (! $auth->isSuccessFul()) {
            return ['success' => false, 'action' => 'error', 'message' => 'Auth failed: '.$auth->message];
        }

        // 2. Ensure ruleset exists
        if (! $this->resolveRulesetId()) {
            $created = $this->createRuleset();
            if (! $created->isSuccessFul()) {
                return ['success' => false, 'action' => 'error', 'message' => 'Could not create ruleset: '.$created->message];
            }
            $this->resolveRulesetId();
        }

        if (! $this->rulesetId) {
            return ['success' => false, 'action' => 'error', 'message' => 'No ruleset ID found'];
        }

        // 3. Build the rule payload
        $payload = [
            'action' => 'rewrite',
            'description' => 'Laravel Headers',
            'enabled' => true,
            'expression' => 'true',
            'action_parameters' => ['headers' => $this->buildHeaderPayload()],
        ];

        // 4. Update existing rule or create new one
        $existing = $this->findLaravelRule();

        if ($existing) {
            $endpoint = '/zones/'.$this->zoneId.'/rulesets/'.$this->rulesetId.'/rules/'.$existing->id;
            $response = ApiResponse::patch($endpoint, $payload);

            return [
                'success' => $response->isSuccessFul(),
                'action' => $response->isSuccessFul() ? 'updated' : 'error',
                'message' => $response->isSuccessFul() ? 'Transform rule updated' : $response->message,
            ];
        }

        $endpoint = '/zones/'.$this->zoneId.'/rulesets/'.$this->rulesetId.'/rules';
        $response = ApiResponse::post($endpoint, $payload);

        return [
            'success' => $response->isSuccessFul(),
            'action' => $response->isSuccessFul() ? 'created' : 'error',
            'message' => $response->isSuccessFul() ? 'Transform rule created' : $response->message,
        ];
    }

    public function deleteRuleSet(string $id): ApiResponse
    {
        return ApiResponse::delete('/zones/'.$this->zoneId.'/rulesets/'.$id);
    }

    public function getRulesetId(): ?string
    {
        $this->resolveRulesetId();

        return $this->rulesetId;
    }

    // ----------------------------------------------------------------------
    // Internal
    // ----------------------------------------------------------------------

    private function resolveRulesetId(): bool
    {
        $response = $this->getResponseHeadersRuleset();

        if ($response->isSuccessFul() && ! empty($response->result->id)) {
            $this->rulesetId = $response->result->id;

            return true;
        }

        return false;
    }

    private function createRuleset(): ApiResponse
    {
        return ApiResponse::post('/zones/'.$this->zoneId.'/rulesets', [
            'name' => 'default',
            'description' => 'via Cloudflare Agent for Laravel',
            'kind' => 'zone',
            'phase' => 'http_request_late_transform',
        ]);
    }

    private function buildHeaderPayload(): array
    {
        $payload = [];
        foreach (self::allHeaders() as $name => $expression) {
            $payload[$name] = [
                'operation' => 'set',
                'expression' => $expression,
            ];
        }

        return $payload;
    }
}
