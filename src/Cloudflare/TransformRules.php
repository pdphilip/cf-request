<?php

namespace PDPhilip\CfRequest\Cloudflare;

// https://developers.cloudflare.com/api/operations/createZoneRuleset

class TransformRules
{
    public mixed $zoneId;

    public mixed $rulesetId;

    public function __construct()
    {
        $this->zoneId = config('cf-request.cloudflare.zoneId');

    }

    public function checkAuth()
    {
        return ApiResponse::get('/zones/'.$this->zoneId.'/rulesets');
    }

    public function setRulesetId(): bool
    {
        $rulesetId = $this->getRulesetId();
        if ($rulesetId) {
            $this->rulesetId = $rulesetId;

            return true;
        }

        return false;
    }

    public function getRulesetId(): ?string
    {
        $current = $this->getResponseHeadersRuleset();
        if ($current->isSuccessFul()) {
            $result = $current->result;
            if (! empty($result->id)) {
                return $result->id;
            }
        }

        return null;
    }

    public function getResponseHeadersRuleset(): ApiResponse
    {

        $endpoint = '/zones/'.$this->zoneId.'/rulesets/phases/http_request_late_transform/entrypoint';

        return ApiResponse::get($endpoint);
    }

    public function deleteRuleSet($id): ApiResponse
    {
        $endpoint = '/zones/'.$this->zoneId.'/rulesets/'.$id;

        return ApiResponse::delete($endpoint);
    }

    public function createRequestHeadersRule(): ApiResponse
    {
        $endpoint = '/zones/'.$this->zoneId.'/rulesets';
        $payload['name'] = 'default';
        $payload['description'] = 'via Cloudflare Agent for Laravel';
        $payload['kind'] = 'zone';
        $payload['phase'] = 'http_request_late_transform';

        return ApiResponse::post($endpoint, $payload);
    }

    public function verifyHeaders(): bool
    {
        $current = $this->getResponseHeadersRuleset();
        if (! empty($current->result->rules)) {
            foreach ($current->result->rules as $rule) {
                if ($rule->description === 'Laravel Headers') {

                    return true;
                }
            }
        }

        return false;
    }

    public function setLaravelHeaders(): ApiResponse
    {
        $auth = $this->checkAuth();
        if (! $auth->isSuccessFul()) {
            return $auth;
        }
        $set = $this->setRulesetId();
        if (! $set) {
            $created = $this->createRequestHeadersRule();
            if (! $created->isSuccessFul()) {
                return $created;
            }
        }
        $this->setRulesetId();
        if ($this->rulesetId) {

            $existing = $this->verifyHeaders();
            if ($existing) {
                return ApiResponse::setOk('required headers have already been set');
            }
            $endpoint = '/zones/'.$this->zoneId.'/rulesets/'.$this->rulesetId.'/rules';
            $payload['action'] = 'rewrite';
            $payload['description'] = 'Laravel Headers';
            $payload['enabled'] = true;
            $payload['expression'] = 'true';
            $payload['action_parameters'] = [
                'headers' => [
                    'X-IP' => [
                        'operation' => 'set',
                        'expression' => 'ip.src',
                    ],
                    'X-AGENT' => [
                        'operation' => 'set',
                        'expression' => 'http.user_agent',
                    ],
                    'X-COUNTRY' => [
                        'operation' => 'set',
                        'expression' => 'ip.src.country',
                    ],
                    'X-CITY' => [
                        'operation' => 'set',
                        'expression' => 'ip.src.city',
                    ],
                    'X-REGION' => [
                        'operation' => 'set',
                        'expression' => 'ip.src.region',
                    ],
                    'X-CONTINENT' => [
                        'operation' => 'set',
                        'expression' => 'ip.src.continent',
                    ],
                    'X-POSTAL-CODE' => [
                        'operation' => 'set',
                        'expression' => 'ip.src.postal_code',
                    ],
                    'X-LAT' => [
                        'operation' => 'set',
                        'expression' => 'ip.src.lat',
                    ],
                    'X-LON' => [
                        'operation' => 'set',
                        'expression' => 'ip.src.lon',
                    ],
                    'X-TIMEZONE' => [
                        'operation' => 'set',
                        'expression' => 'ip.src.timezone.name',
                    ],
                    'X-REFERER' => [
                        'operation' => 'set',
                        'expression' => 'http.referer',
                    ],
                    'X-BOT-SCORE' => [
                        'operation' => 'set',
                        'expression' => 'cf.bot_management.score',
                    ],
                ],

            ];

            return ApiResponse::post($endpoint, $payload);
        }

        return ApiResponse::setError(400, 'No Ruleset ID');
    }
}
